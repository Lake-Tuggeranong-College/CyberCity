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
TABLE = "DockerContainers"
CHALLENGE_ROOT = Path("/var/www/CyberCity/dockerStuff")
MAP_FILE = CHALLENGE_ROOT / "challenge_map.json"  # optional fallback when dockerChallengeID is missing
BASE_PORT, MAX_PORT = 17001, 17999

active_containers = {}  # row_id -> {dockerChallengeID, challengeID, port, delete_time}
TABLE_COLS = []         # populated at boot
HAS_DCID = False        # dockerChallengeID presence

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
    global TABLE_COLS, HAS_DCID
    rows = db_query(f"SHOW COLUMNS FROM {TABLE}")
    # rows: Field, Type, Null, Key, Default, Extra
    TABLE_COLS = [r[0] for r in rows]
    HAS_DCID = 'dockerChallengeID' in TABLE_COLS
    log(f"Detected columns: {TABLE_COLS}; dockerChallengeID present? {HAS_DCID}")

def normalize_binlog_row(data: dict) -> dict:
    """Map UNKNOWN_COLi keys to real column names based on table order."""
    if not data: return {}
    if all(k.startswith("UNKNOWN_COL") for k in data.keys()):
        # keys like UNKNOWN_COL0..N correspond to ordinal columns
        mapped = {}
        for k, v in data.items():
            idx = int(re.search(r"(\d+)$", k).group(1))
            if idx < len(TABLE_COLS):
                mapped[TABLE_COLS[idx]] = v
        return mapped
    return data

def get_next_available_port():
    rows = db_query(f"SELECT port FROM {TABLE} WHERE port IS NOT NULL")
    used = {r[0] for r in rows if r[0]}
    for p in range(BASE_PORT, MAX_PORT + 1):
        if p not in used:
            return p
    raise RuntimeError("No available ports in the specified range.")

def update_port_in_db(row_id, port):
    db_query(f"UPDATE {TABLE} SET port=%s WHERE ID=%s", (port, row_id), fetch=False, commit=True)
    log(f"Port assigned: row {row_id} -> {port}")

# =========================
# CHALLENGE FOLDER RESOLUTION
# =========================
def load_map():
    if MAP_FILE.exists():
        try: return json.loads(MAP_FILE.read_text())
        except Exception: log("Warning: challenge_map.json invalid JSON; ignored.")
    return {}

CH_MAP = load_map()

def resolve_by_dockerChallengeID(dcid: str) -> Path:
    cdir = CHALLENGE_ROOT / str(dcid)
    if not cdir.is_dir():
        raise FileNotFoundError(f"Challenge folder not found for dockerChallengeID='{dcid}' at {cdir}")
    return cdir

def resolve_by_challengeID(challenge_id: str) -> Path:
    """
    Fallback if dockerChallengeID column doesn't exist:
    - If challenge_id maps in challenge_map.json, use that folder name.
    - Else, try a loose match by name.
    """
    cid = str(challenge_id).strip()
    mapped = CH_MAP.get(cid)
    if mapped:
        cdir = CHALLENGE_ROOT / mapped
        if cdir.is_dir(): return cdir
        raise FileNotFoundError(f"Mapped folder '{mapped}' for challengeID='{cid}' not found at {cdir}")

    # Loose match: try to find a folder containing the token
    norm = re.sub(r'[^a-z0-9]+','',cid.lower())
    candidates = []
    for p in CHALLENGE_ROOT.iterdir():
        if not p.is_dir(): continue
        name_norm = re.sub(r'[^a-z0-9]+','',p.name.lower())
        if norm and (name_norm.startswith(norm) or norm in name_norm):
            candidates.append(p)
    if candidates:
        candidates.sort(key=lambda p: (len(p.name), p.name.lower()))
        return candidates[0]
    raise FileNotFoundError(
        f"No folder found for challengeID='{cid}'. Add a mapping in {MAP_FILE} like {{\"{cid}\": \"chmod\"}}."
    )

def resolve_challenge_dir(dockerChallengeID, challengeID) -> Path:
    if dockerChallengeID:
        return resolve_by_dockerChallengeID(dockerChallengeID)
    return resolve_by_challengeID(challengeID)

# =========================
# DOCKER OPS
# =========================
def launch_container(userID, challengeID, dockerChallengeID, port, row_id):
    try:
        cdir = resolve_challenge_dir(dockerChallengeID, challengeID)
    except Exception as e:
        log(e); return

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
        log(f"Launched user {userID} ({dockerChallengeID or challengeID}) on port {port}")
    except subprocess.CalledProcessError as e:
        log(f"Compose up failed (row {row_id}) in {cdir}: {e}")
    except Exception as e:
        log(f"Launch error (row {row_id}): {e}")

def remove_container(userID, challengeID, dockerChallengeID, port, row_id):
    try:
        cdir = resolve_challenge_dir(dockerChallengeID, challengeID)
    except Exception as e:
        log(e)
        try: db_query(f"DELETE FROM {TABLE} WHERE ID=%s", (row_id,), fetch=False, commit=True)
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
        db_query(f"DELETE FROM {TABLE} WHERE ID=%s", (row_id,), fetch=False, commit=True)
        log(f"Removed row {row_id} {dockerChallengeID or challengeID} port {port}")
    except subprocess.CalledProcessError as e:
        log(f"Compose down failed (row {row_id}) in {cdir}: {e}")
    except Exception as e:
        log(f"Remove error (row {row_id}): {e}")

# =========================
# HOUSEKEEPER
# =========================
def time_tracker():
    while True:
        current_time = datetime.now()
        try:
            cols = ["ID","timeInitialised","userID","challengeID","port"]
            if HAS_DCID: cols.insert(4, "dockerChallengeID")  # after challengeID
            rows = db_query(f"SELECT {', '.join(cols)} FROM {TABLE}")

            for row in rows:
                # Unpack according to cols list
                row_dict = dict(zip(cols, row))
                row_id = row_dict.get("ID")
                time_initialised = row_dict.get("timeInitialised")
                user_id = row_dict.get("userID")
                challenge_id = row_dict.get("challengeID")
                docker_challenge_id = row_dict.get("dockerChallengeID") if HAS_DCID else None
                port = row_dict.get("port")

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
# BINLOG PROCESSOR
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
            if getattr(be, "table", None) != TABLE:
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

                row_id             = data.get("ID")
                time_init          = data.get("timeInitialised")
                user_id            = data.get("userID")
                challenge_id       = data.get("challengeID")
                docker_challenge_id= data.get("dockerChallengeID") if HAS_DCID else None
                port               = data.get("port")

                if evt == "DELETE":
                    info = active_containers.pop(row_id, None)
                    if info:
                        remove_container(user_id, challenge_id, info["dockerChallengeID"], info["port"], row_id)
                    continue

                if not time_init:
                    log(f"Row {row_id} missing timeInitialised; skip.")
                    continue

                delete_time = time_init + timedelta(minutes=20)

                if evt == "INSERT":
                    if not port:
                        port = get_next_available_port()
                        update_port_in_db(row_id, port)
                    launch_container(user_id, challenge_id, docker_challenge_id, port, row_id)
                    active_containers[row_id] = {
                        "dockerChallengeID": docker_challenge_id,
                        "challengeID": challenge_id,
                        "port": port,
                        "delete_time": delete_time
                    }
                elif evt == "UPDATE":
                    if row_id in active_containers:
                        ac = active_containers[row_id]
                        ac["delete_time"] = delete_time
                        if HAS_DCID: ac["dockerChallengeID"] = docker_challenge_id or ac["dockerChallengeID"]
                        if port: ac["port"] = port

                # opportunistic sweep
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
    load_cols()  # detect columns & dockerChallengeID availability
    threading.Thread(target=time_tracker, daemon=True).start()
    process_binlog_event()
