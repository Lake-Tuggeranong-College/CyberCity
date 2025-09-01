#!/usr/bin/env python3
from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import time
from datetime import datetime, timedelta
import pymysql
import subprocess
import threading
from pathlib import Path
import traceback
import json
import re

# =========================
# CONFIG
# =========================
DB = {'host':'localhost','user':'LTC','passwd':'LTCpcgame5','port':3306,'db':'CyberCity'}
BINLOG_CONN = {'host':DB['host'],'port':DB['port'],'user':DB['user'],'passwd':DB['passwd']}
TABLE_CONTAINERS = "DockerContainers"   # your table with rows to watch
TABLE_CHALLENGES = "Challenges"         # table that has dockerChallengeID
CHALLENGE_ROOT = Path("/var/www/CyberCity/dockerStuff")
BASE_PORT, MAX_PORT = 17001, 17999

active_containers = {}  # row_id -> {challengeID, dockerChallengeID, port, delete_time}
TABLE_COLS = []         # detected column order for DockerContainers
CACHE = {}              # challengeID -> {"dockerChallengeID": str, "ts": float}
CACHE_TTL = 300         # seconds

def log(*a): print("[DB2Docker]", *a)

# =========================
# DB HELPERS
# =========================
def db_query(sql, params=None, fetch=True, commit=False):
    conn = None
    try:
        conn = pymysql.connect(**DB)
        cur = conn.cursor()
        cur.execute(sql, params or ())
        if commit: conn.commit()
        return cur.fetchall() if fetch else None
    finally:
        if conn: conn.close()

def load_cols():
    """Detect column order for DockerContainers to map UNKNOWN_COLi."""
    global TABLE_COLS
    rows = db_query(f"SHOW COLUMNS FROM {TABLE_CONTAINERS}")
    TABLE_COLS = [r[0] for r in rows]
    log(f"Detected {TABLE_CONTAINERS} columns:", TABLE_COLS)

def normalize_binlog_row(data: dict) -> dict:
    """Map UNKNOWN_COLi keys to real column names based on table order."""
    if not data: return {}
    if all(k.startswith("UNKNOWN_COL") for k in data.keys()):
        mapped = {}
        for k, v in data.items():
            m = re.search(r"(\d+)$", k)
            if not m: continue
            idx = int(m.group(1))
            if idx < len(TABLE_COLS):
                mapped[TABLE_COLS[idx]] = v
        return mapped
    return data

# =========================
# CHALLENGE LOOKUP (via Challenges table)
# =========================
def cache_get(challenge_id: str):
    item = CACHE.get(challenge_id)
    if not item: return None
    if time.time() - item["ts"] > CACHE_TTL:
        CACHE.pop(challenge_id, None)
        return None
    return item["dockerChallengeID"]

def cache_put(challenge_id: str, docker_challenge_id: str):
    CACHE[str(challenge_id)] = {"dockerChallengeID": docker_challenge_id, "ts": time.time()}

def get_docker_challenge_id(challenge_id) -> str:
    """Return dockerChallengeID string for a given Challenges.ID (cached)."""
    if challenge_id is None: return None
    key = str(challenge_id)
    cached = cache_get(key)
    if cached: return cached

    row = db_query(f"SELECT dockerChallengeID FROM {TABLE_CHALLENGES} WHERE ID = %s", (challenge_id,))
    if not row or not row[0][0]:
        return None
    dcid = str(row[0][0])
    cache_put(key, dcid)
    return dcid

# =========================
# PORT MGMT
# =========================
def get_next_available_port():
    rows = db_query(f"SELECT port FROM {TABLE_CONTAINERS} WHERE port IS NOT NULL")
    used = {r[0] for r in rows if r[0]}
    for p in range(BASE_PORT, MAX_PORT + 1):
        if p not in used:
            return p
    raise RuntimeError("No available ports in the specified range.")

def update_port_in_db(row_id, port):
    db_query(f"UPDATE {TABLE_CONTAINERS} SET port=%s WHERE ID=%s", (port, row_id), fetch=False, commit=True)
    log(f"Port assigned: row {row_id} -> {port}")

# =========================
# FOLDER RESOLUTION
# =========================
def resolve_challenge_dir(docker_challenge_id: str) -> Path:
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
    if not dockerChallengeID:
        log(f"Row {row_id}: no dockerChallengeID found for challengeID={challengeID}; skipping launch.")
        return
    try:
        cdir = resolve_challenge_dir(dockerChallengeID)
    except FileNotFoundError as e:
        log(str(e)); return

    compose_file = cdir / "docker-compose.yml"
    env_file     = cdir / ".env"

    try:
        cdir.mkdir(parents=True, exist_ok=True)
        env_file.write_text(f"PORT={port}\nUSER={userID}\n")
    except Exception as e:
        log(f"Error writing .env in {cdir}: {e}"); return

    if not compose_file.exists():
        log(f"Compose file missing: {compose_file}"); return

    log(f"Launching in {cdir}")
    try:
        subprocess.run(
            ["sudo","docker","compose","-p",str(port),"-f",str(compose_file),"up","-d","--build"],
            check=True, cwd=str(cdir)
        )
        log(f"Launched user {userID} ({dockerChallengeID}) on port {port}")
    except subprocess.CalledProcessError as e:
        log(f"Compose up failed (row {row_id}) in {cdir}: {e}")
    except Exception as e:
        log(f"Launch error (row {row_id}): {e}")

def remove_container(userID, challengeID, dockerChallengeID, port, row_id):
    if not dockerChallengeID:
        dockerChallengeID = get_docker_challenge_id(challengeID)

    if not dockerChallengeID:
        log(f"Row {row_id}: cannot resolve dockerChallengeID for challengeID={challengeID}; DB-removing only.")
        try:
            db_query(f"DELETE FROM {TABLE_CONTAINERS} WHERE ID=%s", (row_id,), fetch=False, commit=True)
        except Exception as db_err: log(f"DB cleanup failed for row {row_id}: {db_err}")
        return

    try:
        cdir = resolve_challenge_dir(dockerChallengeID)
    except FileNotFoundError as e:
        log(str(e))
        try: db_query(f"DELETE FROM {TABLE_CONTAINERS} WHERE ID=%s", (row_id,), fetch=False, commit=True)
        except Exception as db_err: log(f"DB cleanup failed for row {row_id}: {db_err}")
        return

    compose_file = cdir / "docker-compose.yml"
    log(f"Removing in {cdir} (port {port})")
    try:
        subprocess.run(
            ["sudo","docker","compose","-p",str(port),"-f",str(compose_file),"down","--volumes","--remove-orphans"],
            check=True, cwd=str(cdir)
        )
        active_containers.pop(row_id, None)
        db_query(f"DELETE FROM {TABLE_CONTAINERS} WHERE ID=%s", (row_id,), fetch=False, commit=True)
        log(f"Removed row {row_id} {dockerChallengeID} port {port}")
    except subprocess.CalledProcessError as e:
        log(f"Compose down failed (row {row_id}) in {cdir}: {e}")
    except Exception as e:
        log(f"Remove error (row {row_id}): {e}")

# =========================
# HOUSEKEEPER (JOIN reads dockerChallengeID)
# =========================
def time_tracker():
    while True:
        current_time = datetime.now()
        try:
            # Pull dockerChallengeID via JOIN so polling never references a missing column
            sql = f"""
            SELECT dc.ID, dc.timeInitialised, dc.userID, dc.challengeID, c.dockerChallengeID, dc.port
            FROM {TABLE_CONTAINERS} dc
            LEFT JOIN {TABLE_CHALLENGES} c ON c.ID = dc.challengeID
            """
            rows = db_query(sql)

            for (row_id, time_initialised, user_id, challenge_id, docker_challenge_id, port) in rows:
                if not time_initialised or not port:
                    continue

                delete_time = time_initialised + timedelta(minutes=20)
                if current_time > delete_time:
                    log(f"expiry hit: row={row_id}")
                    remove_container(user_id, challenge_id, docker_challenge_id, port, row_id)

        except Exception as e:
            log(f"Error polling database for expired containers: {e}\n{traceback.format_exc()}")

        time.sleep(15)

# =========================
# BINLOG PROCESSOR (per-row lookup)
# =========================
def process_binlog_event():
    stream = None
    try:
        stream = BinLogStreamReader(
            connection_settings=BINLOG_CONN,
            server_id=101,
            only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
            blocking=True,
            resume_stream=True
        )
        for be in stream:
            if getattr(be, "table", None) != TABLE_CONTAINERS:
                continue
            for r in be.rows:
                if isinstance(be, WriteRowsEvent):
                    data, evt = r["values"], "INSERT"
                elif isinstance(be, UpdateRowsEvent):
                    data, evt = r["after_values"], "UPDATE"
                else:
                    data, evt = r["values"], "DELETE"

                data = normalize_binlog_row(data)
                log(f"Debug: Event={evt} Data={data}")

                row_id       = data.get("ID")
                time_init    = data.get("timeInitialised")
                user_id      = data.get("userID")
                challenge_id = data.get("challengeID")
                port         = data.get("port")

                # DELETE -> best-effort removal
                if evt == "DELETE":
                    info = active_containers.pop(row_id, None)
                    docker_challenge_id = (info or {}).get("dockerChallengeID") or get_docker_challenge_id(challenge_id)
                    if port is None and info: port = info.get("port")
                    remove_container(user_id, challenge_id, docker_challenge_id, port, row_id)
                    continue

                if not time_init:
                    log(f"Row {row_id} missing timeInitialised; skip.")
                    continue

                docker_challenge_id = get_docker_challenge_id(challenge_id)
                delete_time = time_init + timedelta(minutes=20)

                if evt == "INSERT":
                    if not port:
                        port = get_next_available_port()
                        update_port_in_db(row_id, port)
                    launch_container(user_id, challenge_id, docker_challenge_id, port, row_id)
                    active_containers[row_id] = {
                        "challengeID": challenge_id,
                        "dockerChallengeID": docker_challenge_id,
                        "port": port,
                        "delete_time": delete_time
                    }
                elif evt == "UPDATE":
                    if row_id in active_containers:
                        ac = active_containers[row_id]
                        ac["delete_time"] = delete_time
                        ac["dockerChallengeID"] = docker_challenge_id or ac["dockerChallengeID"]
                        if port: ac["port"] = port

                # Opportunistic sweep
                now = datetime.now()
                for rid, info in list(active_containers.items()):
                    if now > info["delete_time"]:
                        remove_container(user_id, info["challengeID"], info["dockerChallengeID"], info["port"], rid)

    except KeyboardInterrupt:
        log("Stopping binlog monitoring.")
    except Exception as e:
        log(f"Fatal binlog loop error: {e}\n{traceback.format_exc()}")
    finally:
        if stream: stream.close()

# =========================
# BOOT
# =========================
if __name__ == "__main__":
    load_cols()  # get container table column order for binlog mapping
    threading.Thread(target=time_tracker, daemon=True).start()
    process_binlog_event()
