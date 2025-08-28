#!/usr/bin/env python3
from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import time
from datetime import datetime, timedelta
import pymysql
import os
import subprocess
import threading
import random
import traceback
import shutil

# =========================
# DEBUG / VERBOSITY
# =========================
DEBUG = True  # <-- set to False to silence debug prints

def debug(*args, **kwargs):
    if DEBUG:
        print(*args, **kwargs)

# =========================
# CONFIG
# =========================

db_config = {
    'host': '10.177.202.196',
    'user': 'CyberCity',
    'passwd': 'CyberCity',
    'port': 3306,
    'database': 'CyberCity',
    'charset': 'utf8mb4'
}

# Root where your per-challenge folders live
DOCKER_STUFF_ROOT = "/var/www/CyberCity/dockerStuff"

# Port range for dynamic allocation
BASE_PORT = 17001
MAX_PORT = 17999

# How long containers live before automatic shutdown
TTL_MINUTES = 20

# How often the time_tracker polls DB for stale containers
TRACKER_POLL_SECONDS = 15

# Poller interval (fallback when binlog perms are missing)
NEWROW_POLL_SECONDS = 2

# Compose binary check
DOCKER_BIN = shutil.which("docker") or "/usr/bin/docker"

# =========================
# STATE
# =========================

# Tracks active containers by DB row_id
#   row_id: {"userID": int, "challengeID": (int|str), "port": int, "delete_time": datetime}
active_containers = {}

# Tracks which DB rows we have already handled in polling mode
processed_rows = set()

# =========================
# DB helpers
# =========================

def db_conn():
    try:
        con = pymysql.connect(**db_config)
        return con
    except Exception as e:
        print(f"[FATAL] Could not connect to MySQL: {e}")
        if DEBUG:
            traceback.print_exc()
        raise

def resolve_chal_folder(challengeID) -> str:
    """
    Returns the folder name for the challenge.
    Accepts either numeric Challenges.ID or a slug (as stored in DockerContainers.challengeID).
    """
    # If it's a slug already, just use it
    s = str(challengeID)
    if not s.isdigit():
        folder = s
        debug(f"[resolve_chal_folder] slug detected -> folder={folder}")
        return folder

    con = db_conn()
    try:
        with con.cursor() as c:
            c.execute("SELECT dockerChallengeID FROM Challenges WHERE ID=%s", (int(challengeID),))
            row = c.fetchone()
            if not row or not row[0]:
                raise RuntimeError(f"No dockerChallengeID for challenge {challengeID}")
            folder = str(row[0])
            debug(f"[resolve_chal_folder] challengeID={challengeID} -> folder={folder}")
            return folder
    finally:
        con.close()

def get_used_ports() -> set:
    con = db_conn()
    try:
        with con.cursor() as c:
            c.execute("SELECT port FROM DockerContainers WHERE port IS NOT NULL")
            res = c.fetchall()
            ports = {int(r[0]) for r in res if r[0] is not None}
            debug(f"[get_used_ports] currently used: {sorted(ports)}")
            return ports
    finally:
        con.close()

def get_next_available_port() -> int:
    used = get_used_ports()
    debug("[get_next_available_port] scanning range for free port")
    start = random.randint(BASE_PORT, MAX_PORT)
    ports = list(range(start, MAX_PORT + 1)) + list(range(BASE_PORT, start))
    for p in ports:
        if p not in used:
            debug(f"[get_next_available_port] found free port {p}")
            return p
    raise RuntimeError("No available ports in the specified range.")

def update_port_in_db(assigned_port: int, row_id: int):
    con = db_conn()
    try:
        with con.cursor() as c:
            c.execute("UPDATE DockerContainers SET port=%s WHERE ID=%s", (assigned_port, row_id))
        con.commit()
        debug(f"[update_port_in_db] row {row_id} -> port {assigned_port}")
    finally:
        con.close()

def delete_row_from_db(row_id: int, challengeID):
    con = db_conn()
    try:
        with con.cursor() as c:
            c.execute("DELETE FROM DockerContainers WHERE ID=%s AND challengeID=%s", (row_id, challengeID))
        con.commit()
        debug(f"[delete_row_from_db] removed DB row id={row_id}, challengeID={challengeID}")
    finally:
        con.close()

# =========================
# Sanity checks (debug)
# =========================

def _check_docker_available():
    if not DOCKER_BIN:
        raise RuntimeError("docker binary not found in PATH")
    try:
        subprocess.run([DOCKER_BIN, "--version"], check=True, capture_output=True)
        debug("[check] docker is available")
    except Exception as e:
        raise RuntimeError(f"Docker not available: {e}")

def _check_compose_support():
    try:
        subprocess.run([DOCKER_BIN, "compose", "version"], check=True, capture_output=True)
        debug("[check] docker compose v2 is available")
    except Exception as e:
        raise RuntimeError(f"Docker compose v2 not available: {e}")

# =========================
# Docker compose helpers
# =========================

def write_env_file(chal_dir: str, userID: int, port: int):
    env_file_path = os.path.join(chal_dir, ".env")
    with open(env_file_path, "w") as f:
        f.write(f"PORT={port}\n")
        f.write(f"USER={userID}\n")
    debug(f"[write_env_file] wrote .env at {env_file_path} with PORT={port} USER={userID}")

def launch_container(userID: int, challengeID, port: int, row_id: int):
    """
    Runs `docker compose up -d --build` in the challenge folder with project name = port.
    Assumes compose reads .env for PORT and USER.
    """
    try:
        folder = resolve_chal_folder(challengeID)
        chal_dir = os.path.join(DOCKER_STUFF_ROOT, folder)
        compose_file_path = os.path.join(chal_dir, "docker-compose.yml")

        if not os.path.isdir(chal_dir):
            raise RuntimeError(f"Challenge folder not found: {chal_dir}")
        if not os.path.isfile(compose_file_path):
            raise RuntimeError(f"docker-compose.yml not found: {compose_file_path}")

        write_env_file(chal_dir, userID, port)

        debug(f"[launch_container] starting compose in {chal_dir} on port {port} (project={port})")
        subprocess.run(
            [DOCKER_BIN, "compose", "-p", str(port), "-f", "docker-compose.yml", "up", "-d", "--build"],
            cwd=chal_dir,
            check=True
        )

        print(f"Launched container for user {userID} challenge {challengeID} on port {port}")

        delete_time = datetime.now() + timedelta(minutes=TTL_MINUTES)
        active_containers[row_id] = {
            "userID": userID,
            "challengeID": challengeID,
            "port": port,
            "delete_time": delete_time
        }
        debug(f"[launch_container] scheduled delete at {delete_time}")

    except Exception as e:
        print(f"Error while launching container for user {userID} challenge {challengeID}: {e}")
        if DEBUG:
            traceback.print_exc()

def remove_container(userID: int, challengeID, port: int, row_id: int):
    """
    Runs `docker compose down` in the challenge folder for project name = port, then
    deletes the DockerContainers DB row.
    """
    try:
        folder = resolve_chal_folder(challengeID)
        chal_dir = os.path.join(DOCKER_STUFF_ROOT, folder)
        compose_file_path = os.path.join(chal_dir, "docker-compose.yml")

        if not os.path.isdir(chal_dir) or not os.path.isfile(compose_file_path):
            debug(f"[remove_container][WARN] compose path missing for removal: {compose_file_path}")

        print(f"Attempting to remove container on port: {port}")
        subprocess.run(
            [DOCKER_BIN, "compose", "-p", str(port), "-f", "docker-compose.yml", "down", "--volumes", "--remove-orphans"],
            cwd=chal_dir,
            check=True
        )

        print(f"Completely removed container for user {userID}, row {row_id}, challenge {challengeID} at port {port}")

        active_containers.pop(row_id, None)
        delete_row_from_db(row_id, challengeID)

    except Exception as e:
        print(f"Error while stopping container for user {userID}, row {row_id}: {e}")
        if DEBUG:
            traceback.print_exc()

# =========================
# Background TTL tracker
# =========================

def time_tracker():
    """
    Periodically polls DB for any rows whose timeInitialised + TTL are past due,
    and removes those containers. Also cross-checks local active_containers TTLs.
    """
    while True:
        try:
            current_time = datetime.now()
            debug("[time_tracker] scanning for expired containers")

            con = db_conn()
            with con.cursor() as c:
                c.execute("SELECT ID, timeInitialised, userID, challengeID, port FROM DockerContainers")
                rows = c.fetchall()

            for row in rows:
                row_id, time_initialised, user_id, challenge_id, port = row
                if not time_initialised:
                    continue

                delete_time = time_initialised + timedelta(minutes=TTL_MINUTES)
                if port and current_time > delete_time:
                    print(f"Located expired container row {row_id}.. attempting to remove")
                    remove_container(user_id, challenge_id, int(port), row_id)

            for rid, info in list(active_containers.items()):
                if datetime.now() > info["delete_time"]:
                    print(f"Local TTL expired for row {rid}, removing")
                    remove_container(info["userID"], info["challengeID"], info["port"], rid)

        except Exception as e:
            print(f"Error in time_tracker: {e}")
            if DEBUG:
                traceback.print_exc()
        finally:
            try:
                con.close()
            except Exception:
                pass

        time.sleep(TRACKER_POLL_SECONDS)

# =========================
# Shared row processing
# =========================

def _process_insert_like(row_id, time_initialised, userID, challengeID, port):
    """
    Common logic for INSERT (binlog) or NEW ROW (polling).
    Allocates a port if needed, launches container, and sets local TTL.
    """
    if row_id is None or userID is None or challengeID is None or not time_initialised:
        debug("[row] missing required fields; skipping")
        return

    # Allocate port and update DB (retry a few times in case of race)
    if not port:
        for attempt in range(5):
            try:
                assigned_port = get_next_available_port()
                update_port_in_db(assigned_port, row_id)
                port = assigned_port
                break
            except Exception as e:
                debug(f"[row] Port allocation/update failed (attempt {attempt+1}): {e}")
                time.sleep(0.2)
        if not port:
            print("[ERROR] Could not allocate a port; skipping launch")
            return

    # Launch container
    launch_container(int(userID), challengeID, int(port), int(row_id))

    # Schedule local TTL (DB poller also enforces)
    delete_time = time_initialised + timedelta(minutes=TTL_MINUTES)
    active_containers[int(row_id)] = {
        "userID": int(userID),
        "challengeID": challengeID,
        "port": int(port),
        "delete_time": delete_time
    }
    debug(f"[row] delete_time for row {row_id} -> {delete_time}")

# =========================
# Binlog processing (preferred)
# =========================

def process_binlog_event():
    """
    Watches MySQL binlog for INSERT/UPDATE/DELETE on DockerContainers.
    """
    stream = BinLogStreamReader(
        connection_settings=db_config,
        server_id=101,  # must be unique and not equal to MySQL server-id
        only_schemas=[db_config['database']],
        only_tables=['DockerContainers'],
        only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
        blocking=True,
        resume_stream=True
    )

    try:
        for binlogevent in stream:
            try:
                if getattr(binlogevent, "table", None) != "DockerContainers":
                    continue

                for row in binlogevent.rows:
                    if isinstance(binlogevent, WriteRowsEvent):
                        data = row["values"]
                        event_type = "INSERT"
                    elif isinstance(binlogevent, UpdateRowsEvent):
                        data = row["after_values"]
                        event_type = "UPDATE"
                    elif isinstance(binlogevent, DeleteRowsEvent):
                        data = row["values"]
                        event_type = "DELETE"
                    else:
                        continue

                    debug(f"[binlog] Event={event_type}, Data={data}")

                    row_id           = data.get('ID')
                    time_initialised = data.get('timeInitialised')
                    userID           = data.get('userID')
                    challengeID      = data.get('challengeID')  # may be slug or numeric
                    port             = data.get('port')

                    if event_type == "INSERT":
                        _process_insert_like(row_id, time_initialised, userID, challengeID, port)

                    elif event_type == "UPDATE":
                        if row_id is not None and time_initialised:
                            delete_time = time_initialised + timedelta(minutes=TTL_MINUTES)
                            if row_id in active_containers:
                                active_containers[row_id]["delete_time"] = delete_time
                                debug(f"[binlog] refreshed TTL for row {row_id} -> {delete_time}")

                    elif event_type == "DELETE":
                        if row_id in active_containers:
                            debug(f"[binlog] row {row_id} deleted in DB; clearing local tracking")
                            active_containers.pop(row_id, None)

            except Exception as inner:
                print(f"[Binlog loop] Error handling event: {inner}")
                if DEBUG:
                    traceback.print_exc()

    except KeyboardInterrupt:
        print("Stopping the binlog monitoring.")
    finally:
        try:
            stream.close()
        except Exception:
            pass

# =========================
# Polling fallback (no binlog privileges)
# =========================

def poll_for_new_rows():
    """
    Fallback watcher that polls DockerContainers for new rows needing a port/launch.
    Does not require REPLICATION privileges.
    """
    debug("[poller] starting DB polling fallback")
    while True:
        try:
            con = db_conn()
            with con.cursor() as c:
                # Pick rows that look like "start requests"
                c.execute("""
                    SELECT ID, timeInitialised, userID, challengeID, port
                    FROM DockerContainers
                    WHERE timeInitialised IS NOT NULL
                      AND (port IS NULL OR port = 0)
                    ORDER BY ID ASC
                """)
                rows = c.fetchall()

            for row in rows:
                row_id, time_initialised, userID, challengeID, port = row
                if row_id in processed_rows:
                    continue
                processed_rows.add(row_id)
                debug(f"[poller] discovered row needing launch: id={row_id}, chal={challengeID}, user={userID}")

                _process_insert_like(row_id, time_initialised, userID, challengeID, port)

        except Exception as e:
            print(f"[poller] error: {e}")
            if DEBUG:
                traceback.print_exc()
        finally:
            try:
                con.close()
            except Exception:
                pass

        time.sleep(NEWROW_POLL_SECONDS)

# =========================
# Main
# =========================

if __name__ == "__main__":
    try:
        _check_docker_available()
        _check_compose_support()
    except Exception as e:
        print(f("[FATAL] Environment check failed: {e}"))
        if DEBUG:
            traceback.print_exc()
        raise

    # Start TTL tracker thread
    time_thread = threading.Thread(target=time_tracker, daemon=True)
    time_thread.start()

    # Try binlog watcher; if perms are missing, fall back to polling
    try:
        process_binlog_event()
    except pymysql.err.OperationalError as e:
        # MySQL error 1227 is "Access denied; need REPLICATION CLIENT/SUPER"
        if getattr(e, "args", [None])[0] == 1227:
            print("[warn] Missing REPLICATION privileges for binlog. Falling back to polling mode.")
            poll_for_new_rows()  # blocking
        else:
            raise
    except Exception as e:
        print(f"[warn] Binlog watcher failed ({e}). Falling back to polling mode.")
        if DEBUG:
            traceback.print_exc()
        poll_for_new_rows()  # blocking
