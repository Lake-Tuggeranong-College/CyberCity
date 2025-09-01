#!/usr/bin/env python3
from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import time
from datetime import datetime, timedelta
import pymysql
import os
import subprocess
import threading
from pathlib import Path
import traceback

# =========================
# CONFIG
# =========================
DB = {
    'host': 'localhost',
    'user': 'LTC',
    'passwd': 'LTCpcgame5',
    'port': 3306,
    'db': 'CyberCity',
}

BINLOG_CONN = {
    'host': DB['host'],
    'port': DB['port'],
    'user': DB['user'],
    'passwd': DB['passwd'],
}

BASE_PORT = 17001
MAX_PORT  = 17999
CHALLENGE_ROOT = Path("/var/www/CyberCity/dockerStuff")
TABLE = "DockerContainers"  # ensure exact case

# In-memory tracker: row_id -> {challengeID, dockerChallengeID, port, delete_time}
active_containers = {}

def log(*args):
    print("[DB2Docker]", *args)

# =========================
# DB HELPERS
# =========================
def db_query(sql, params=None, fetch=True, commit=False):
    conn = None
    try:
        conn = pymysql.connect(**DB)
        cur = conn.cursor()
        cur.execute(sql, params or ())
        if commit:
            conn.commit()
        return cur.fetchall() if fetch else None
    finally:
        if conn:
            conn.close()

def get_next_available_port():
    rows = db_query(f"SELECT port FROM {TABLE} WHERE port IS NOT NULL")
    used = {r[0] for r in rows if r[0]}
    log("attempting to get next available port")
    for p in range(BASE_PORT, MAX_PORT + 1):
        if p not in used:
            log("successfully located next available port. PORT:", p)
            return p
    raise RuntimeError("No available ports in the specified range.")

def update_port_in_db(row_id, assigned_port):
    db_query(f"UPDATE {TABLE} SET port = %s WHERE ID = %s",
             (assigned_port, row_id),
             fetch=False, commit=True)
    log(f"Database updated: ROW {row_id} -> port {assigned_port}")

# =========================
# CHALLENGE RESOLUTION
# =========================
def resolve_challenge_dir(docker_challenge_id: str) -> Path:
    """
    Resolve folder directly by dockerChallengeID (must match folder name).
    """
    cdir = CHALLENGE_ROOT / str(docker_challenge_id)
    if not cdir.is_dir():
        raise FileNotFoundError(
            f"Challenge folder not found for dockerChallengeID='{docker_challenge_id}' at {cdir}"
        )
    return cdir

# =========================
# DOCKER OPS
# =========================
def launch_container(userID, challengeID, dockerChallengeID, port, row_id):
    try:
        cdir = resolve_challenge_dir(dockerChallengeID)
    except FileNotFoundError as e:
        log(str(e))
        return

    compose_file = cdir / "docker-compose.yml"
    env_file     = cdir / ".env"

    try:
        cdir.mkdir(parents=True, exist_ok=True)
        with open(env_file, 'w') as f:
            f.write(f"PORT={port}\n")
            f.write(f"USER={userID}\n")
    except Exception as e:
        log(f"Error writing .env in {cdir}: {e}")
        return

    if not compose_file.exists():
        log(f"Compose file missing: {compose_file}")
        return

    log(f"attempting to launch container in {cdir}")
    try:
        subprocess.run(
            ["sudo", "docker", "compose", "-p", str(port), "-f", str(compose_file), "up", "-d", "--build"],
            check=True,
            cwd=str(cdir)
        )
        log(f"Launched container for user {userID} ({dockerChallengeID}) on port {port}")
    except subprocess.CalledProcessError as e:
        log(f"Compose up failed for row {row_id} in {cdir}: {e}")
    except Exception as e:
        log(f"Error while launching container for row {row_id}: {e}")

def remove_container(userID, challengeID, dockerChallengeID, port, row_id):
    try:
        cdir = resolve_challenge_dir(dockerChallengeID)
    except FileNotFoundError as e:
        log(str(e))
        # Still try to delete DB row to avoid stuck records
        try:
            db_query(f"DELETE FROM {TABLE} WHERE ID = %s", (row_id,), fetch=False, commit=True)
        except Exception as db_err:
            log(f"DB cleanup failed for row {row_id}: {db_err}")
        return

    compose_file = cdir / "docker-compose.yml"

    log(f"Attempting to remove container in {cdir} on port: {port}")
    try:
        subprocess.run(
            ["sudo", "docker", "compose", "-p", str(port), "-f", str(compose_file), "down", "--volumes", "--remove-orphans"],
            check=True,
            cwd=str(cdir)
        )
        log(f"Completely removed container for row {row_id}, challenge {dockerChallengeID}, port {port}")

        active_containers.pop(row_id, None)

        # Remove DB entry
        try:
            db_query(f"DELETE FROM {TABLE} WHERE ID = %s", (row_id,), fetch=False, commit=True)
            log(f"Database entry removed for row {row_id}")
        except Exception as db_error:
            log(f"Error while removing DB entry for row {row_id}: {db_error}")

    except subprocess.CalledProcessError as e:
        log(f"Compose down failed for row {row_id} in {cdir}: {e}")
    except Exception as e:
        log(f"Error while stopping container for row {row_id}: {e}")

# =========================
# HOUSEKEEPER
# =========================
def time_tracker():
    while True:
        current_time = datetime.now()
        try:
            cols = "ID, timeInitialised, userID, challengeID, dockerChallengeID, port"
            rows = db_query(f"SELECT {cols} FROM {TABLE}")

            for row in rows:
                row_id, time_initialised, user_id, challenge_id, docker_challenge_id, port = row

                # Skip rows without a time or port (not yet launched)
                if not time_initialised or not port:
                    continue

                delete_time = time_initialised + timedelta(minutes=20)
                if current_time > delete_time:
                    log(f"located container row={row_id}.. attempting to remove")
                    remove_container(user_id, challenge_id, docker_challenge_id, port, row_id)

        except Exception as e:
            log(f"Error polling database for expired containers: {e}\n{traceback.format_exc()}")

        time.sleep(15)

# =========================
# BINLOG PROCESSOR
# =========================
def process_binlog_event():
    stream = None
    try:
        stream = BinLogStreamReader(
            connection_settings=BINLOG_CONN,
            server_id=101,  # unique vs MySQL server-id
            only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
            blocking=True,
            resume_stream=True
        )

        for binlogevent in stream:
            if getattr(binlogevent, "table", None) != TABLE:
                continue

            for row in binlogevent.rows:
                if isinstance(binlogevent, WriteRowsEvent):
                    data = row["values"]
                    event = "INSERT"
                elif isinstance(binlogevent, UpdateRowsEvent):
                    data = row["after_values"]
                    event = "UPDATE"
                elif isinstance(binlogevent, DeleteRowsEvent):
                    data = row["values"]
                    event = "DELETE"
                else:
                    continue

                log(f"Debug: Event={event} Data={data}")

                # Pull columns (be strict with names)
                row_id             = data.get('ID')
                time_init          = data.get('timeInitialised')
                user_id            = data.get('userID')
                challenge_id       = data.get('challengeID')        # kept for reference
                docker_challenge_id= data.get('dockerChallengeID')  # <-- folder key
                port               = data.get('port')

                # DELETE: try to stop container if we know it; otherwise do nothing
                if event == "DELETE":
                    info = active_containers.pop(row_id, None)
                    if info:
                        remove_container(user_id, challenge_id, info["dockerChallengeID"], info["port"], row_id)
                    continue

                # If we don't have dockerChallengeID, we cannot resolve the folder
                if not docker_challenge_id:
                    log(f"Row {row_id} missing dockerChallengeID; skipping.")
                    continue

                # MySQL returns datetime already; compute delete_time from DB time
                if not time_init:
                    log(f"Row {row_id} has no timeInitialised; skipping launch/extend.")
                    continue

                delete_time = time_init + timedelta(minutes=20)

                if event == "INSERT":
                    # Allocate port if missing
                    if not port:
                        port = get_next_available_port()
                        update_port_in_db(row_id, port)

                    launch_container(user_id, challenge_id, docker_challenge_id, port, row_id)

                    # Track for expiry checks
                    active_containers[row_id] = {
                        "challengeID": challenge_id,
                        "dockerChallengeID": docker_challenge_id,
                        "port": port,
                        "delete_time": delete_time
                    }
                    log(f"Container deletion time for row {row_id}: {delete_time}")

                elif event == "UPDATE":
                    # Extend expiry if tracked
                    if row_id in active_containers:
                        active_containers[row_id]["delete_time"] = delete_time
                        # allow challenge folder or port changes to propagate
                        if docker_challenge_id:
                            active_containers[row_id]["dockerChallengeID"] = docker_challenge_id
                        if port:
                            active_containers[row_id]["port"] = port
                        log(f"Updated deletion time for row {row_id} to {delete_time}")

                # Opportunistic expiry sweep after each event
                now = datetime.now()
                for rid, info in list(active_containers.items()):
                    if now > info["delete_time"]:
                        log(f"removing container row {rid} with delete time {info['delete_time']} because current time is {now}")
                        remove_container(user_id, info["challengeID"], info["dockerChallengeID"], info["port"], rid)

    except KeyboardInterrupt:
        log("Stopping the binlog monitoring.")
    except Exception as e:
        log(f"Fatal error in binlog loop: {e}\n{traceback.format_exc()}")
    finally:
        if stream:
            stream.close()

# =========================
# BOOT
# =========================
if __name__ == "__main__":
    # Start background expiry poller
    threading.Thread(target=time_tracker, daemon=True).start()
    # Start binlog watcher
    process_binlog_event()
