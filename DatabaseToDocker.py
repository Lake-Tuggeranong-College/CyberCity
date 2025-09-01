#!/usr/bin/env python3
from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import time
from datetime import datetime, timedelta
import pymysql
import os
import subprocess
import threading

# =========================
# CONFIG
# =========================
DB = {
    "host": "localhost",
    "user": "LTC",
    "passwd": "LTCpcgame5",
    "port": 3306,
    "db": "CyberCity",
    "charset": "utf8mb4",
    "cursorclass": pymysql.cursors.DictCursor,  # <-- name-based access
}
TABLE = "DockerContainers"  # columns: ID, timeInitialised, userID, challengeID(text), port
DOCKER_ROOT = "/var/www/CyberCity/dockerStuff"  # expects {challengeID}/docker-compose.yml
BASE_PORT = 17001
MAX_PORT = 17999
REMOVE_AFTER_MINUTES = 20
POLL_SECONDS = 15
BINLOG_SERVER_ID = 101  # must be unique vs mysqld server-id

# =========================
# HELPERS
# =========================
def get_conn():
    return pymysql.connect(**DB)

def safe_close(conn):
    try:
        if conn:
            conn.close()
    except Exception:
        pass

def path_for_challenge(challenge_id: str):
    # challengeID is TEXT in DB, so treat as string everywhere (including path)
    base = os.path.join(DOCKER_ROOT, str(challenge_id))
    compose = os.path.join(base, "docker-compose.yml")
    envfile = os.path.join(base, ".env")
    return base, compose, envfile

def ensure_challenge_paths(challenge_id: str):
    base, compose, envfile = path_for_challenge(challenge_id)
    if not os.path.isdir(base):
        os.makedirs(base, exist_ok=True)
    if not os.path.isfile(compose):
        raise FileNotFoundError(f"docker-compose.yml not found for challenge {challenge_id} at {compose}")
    return base, compose, envfile

# =========================
# PORT MANAGEMENT
# =========================
def get_next_available_port():
    conn = None
    try:
        conn = get_conn()
        with conn.cursor() as cur:
            cur.execute(f"SELECT port FROM {TABLE} WHERE port IS NOT NULL")
            rows = cur.fetchall()
            used = {int(r["port"]) for r in rows if r["port"] is not None}
    finally:
        safe_close(conn)

    for p in range(BASE_PORT, MAX_PORT + 1):
        if p not in used:
            print("Allocated port:", p)
            return p
    raise RuntimeError("No available ports in the specified range.")

def update_port_in_db(row_id: int, assigned_port: int):
    conn = None
    try:
        conn = get_conn()
        with conn.cursor() as cur:
            cur.execute(f"UPDATE {TABLE} SET port=%s WHERE ID=%s", (assigned_port, row_id))
        conn.commit()
        print(f"DB updated: row {row_id} -> port {assigned_port}")
    finally:
        safe_close(conn)

# =========================
# DOCKER LIFECYCLE
# =========================
active_containers = {}  # key: row_id; value: {"challengeID": str, "port": int, "delete_time": dt}

def launch_container(user_id: int, challenge_id: str, port: int, row_id: int):
    _, compose, envfile = ensure_challenge_paths(challenge_id)

    # Write env for compose
    with open(envfile, "w") as f:
        f.write(f"PORT={port}\nUSER={user_id}\n")

    print(f"Launching container: challenge='{challenge_id}' port={port} uid={user_id}")

    subprocess.run(
        ["sudo", "docker", "compose", "-p", str(port), "-f", compose, "up", "-d", "--build"],
        check=True,
    )
    print("Container launched.")

def remove_container(user_id: int, challenge_id: str, port: int, row_id: int):
    try:
        _, compose, _ = ensure_challenge_paths(challenge_id)
    except FileNotFoundError:
        compose = None

    print(f"Removing container: row={row_id} challenge='{challenge_id}' port={port} uid={user_id}")
    try:
        if compose and os.path.isfile(compose):
            subprocess.run(
                ["sudo", "docker", "compose", "-p", str(port), "-f", compose, "down", "--volumes", "--remove-orphans"],
                check=True,
            )
        else:
            subprocess.run(
                ["sudo", "docker", "compose", "-p", str(port), "down", "--volumes", "--remove-orphans"],
                check=True,
            )
        print("Compose down complete.")
    except subprocess.CalledProcessError as e:
        print(f"Compose down failed: {e}")

    # Clean DB row
    conn = None
    try:
        conn = get_conn()
        with conn.cursor() as cur:
            cur.execute(f"DELETE FROM {TABLE} WHERE ID=%s AND challengeID=%s", (row_id, challenge_id))
        conn.commit()
        print(f"DB row removed for row={row_id}, challenge='{challenge_id}'")
    except Exception as e:
        print(f"DB delete error for row={row_id}: {e}")
    finally:
        safe_close(conn)

    active_containers.pop(row_id, None)

# =========================
# POLLER (failsafe cleanup)
# =========================
def time_tracker():
    while True:
        current_time = datetime.now()
        conn = None
        try:
            print("Polling for expired containers...")
            conn = get_conn()
            with conn.cursor() as cur:
                cur.execute(f"SELECT ID, timeInitialised, userID, challengeID, port FROM {TABLE}")
                for row in cur.fetchall():
                    row_id = int(row["ID"])
                    t_init = row["timeInitialised"]
                    user_id = int(row["userID"])
                    challenge_id = str(row["challengeID"])
                    port = row["port"]
                    if t_init is None or port is None:
                        continue
                    port = int(port)
                    delete_time = t_init + timedelta(minutes=REMOVE_AFTER_MINUTES)
                    if current_time > delete_time:
                        print(f"Expired: row={row_id} port={port} delete_time={delete_time} now={current_time}")
                        remove_container(user_id, challenge_id, port, row_id)
        except Exception as e:
            print(f"Error polling DB: {e}")
        finally:
            safe_close(conn)

        time.sleep(POLL_SECONDS)

# =========================
# BINLOG HANDLER
# =========================
stream = BinLogStreamReader(
    connection_settings=DB,
    server_id=BINLOG_SERVER_ID,
    only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
    blocking=True,
    resume_stream=True,
)

def process_binlog_event():
    for ev in stream:
        if getattr(ev, "table", None) != TABLE:
            continue

        for row in ev.rows:
            if isinstance(ev, WriteRowsEvent):
                data = row["values"]
                event_type = "INSERT"
            elif isinstance(ev, UpdateRowsEvent):
                data = row["after_values"]
                event_type = "UPDATE"
            elif isinstance(ev, DeleteRowsEvent):
                data = row["values"]
                event_type = "DELETE"
            else:
                continue

            row_id = int(data.get("ID")) if data.get("ID") is not None else None
            user_id = int(data.get("userID")) if data.get("userID") is not None else None
            challenge_id = str(data.get("challengeID")) if data.get("challengeID") is not None else None  # TEXT
            t_init = data.get("timeInitialised")

            print(f"Binlog {event_type}: row_id={row_id} user={user_id} challenge='{challenge_id}' t_init={t_init}")

            if event_type == "DELETE":
                info = active_containers.pop(row_id, None)
                if info:
                    try:
                        remove_container(user_id or 0, challenge_id or info["challengeID"], info["port"], row_id)
                    except Exception as e:
                        print(f"Remove on DELETE failed: {e}")
                continue

            if not t_init or row_id is None or user_id is None or challenge_id is None:
                continue

            creation_time = t_init
            delete_time = creation_time + timedelta(minutes=REMOVE_AFTER_MINUTES)

            if event_type == "INSERT":
                try:
                    port = get_next_available_port()
                    update_port_in_db(row_id, port)
                    launch_container(user_id, challenge_id, port, row_id)
                    active_containers[row_id] = {
                        "challengeID": challenge_id,
                        "port": port,
                        "delete_time": delete_time,
                    }
                    print(f"Scheduled deletion for row={row_id} at {delete_time}")
                except Exception as e:
                    print(f"Launch failed for row={row_id}: {e}")

            elif event_type == "UPDATE":
                if row_id in active_containers:
                    active_containers[row_id]["delete_time"] = delete_time
                    print(f"Extended deletion for row={row_id} -> {delete_time}")

        # After each event batch, sweep any expired (extra safety)
        now = datetime.now()
        for rid, info in list(active_containers.items()):
            if now > info["delete_time"]:
                conn = None
                try:
                    conn = get_conn()
                    with conn.cursor() as cur:
                        cur.execute(f"SELECT userID, challengeID, port FROM {TABLE} WHERE ID=%s", (rid,))
                        r = cur.fetchone()
                    if r:
                        user_id_db = int(r["userID"])
                        challenge_id_db = str(r["challengeID"])
                        port_db = int(r["port"])
                        print(f"Binlog sweep expired row={rid}")
                        remove_container(user_id_db, challenge_id_db, port_db, rid)
                    else:
                        print(f"Row {rid} missing in DB; attempting cleanup with cached info.")
                        remove_container(0, info["challengeID"], info["port"], rid)
                except Exception as e:
                    print(f"Sweep removal error for row={rid}: {e}")
                finally:
                    safe_close(conn)

# =========================
# START
# =========================
if __name__ == "__main__":
    t = threading.Thread(target=time_tracker, daemon=True)
    t.start()
    try:
        process_binlog_event()
    except KeyboardInterrupt:
        print("Stopping binlog monitoring.")
    finally:
        stream.close()
