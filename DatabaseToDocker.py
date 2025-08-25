from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
#import docker
import time
from datetime import datetime, timedelta
import pymysql
import os
import subprocess
import threading


def time_tracker():
    while True:
        current_time = datetime.now()
        
        try:
            print("Searching for containers that should be removed")
            connection = pymysql.connect(**db_config)
            with connection.cursor() as cursor:
                # Query to fetch all active containers with their initialized times
                sql = "SELECT ID, timeInitialised, userID, challengeID, port FROM docker_containers"
                cursor.execute(sql)
                containers = cursor.fetchall()
                
                # Process each container to check if it's expired
                for row in containers:
                    row_id = row[0]             # Column 1: ID
                    time_initialised = row[1]   # Column 2: timeInitialised
                    user_id = row[2]            # Column 3: userID
                    challenge_id = row[3]       # Column 4: challengeID
                    port = row[4]               # Column 5: port
                    
                    # Calculate the delete time (20 minutes after initialization)
                    delete_time = time_initialised + timedelta(minutes=20)
                    
                    # If the container is expired, remove it
                    if current_time > delete_time:
                        print("located container.. attempting to remove")
                        remove_container(user_id, challenge_id, port, row_id)
        
        except Exception as e:
            print(f"Error polling database for expired containers: {e}")
        finally:
            connection.close()
        
        # Sleep for 15 seconds before polling again
        time.sleep(15)


# MySQL connection settings
db_config = {
    'host': 'localhost',
    'user': 'LTC',
    'passwd': 'LTCpcgame5',
    'port': 3306,
    'db': 'CyberCity'  # Replace with your actual database name
}

# Store active containers and their creation timestamps
active_containers = {}

# Track the base and maximum ports
BASE_PORT = 17001
MAX_PORT = 17999

# Function to find the next available port
def get_next_available_port():
    used_ports = set()
    
    # Connect to the MySQL database to check currently used ports
    try:
        connection = pymysql.connect(**db_config)
        with connection.cursor() as cursor:
            cursor.execute("SELECT port FROM docker_containers WHERE port IS NOT NULL")
            result = cursor.fetchall()
            used_ports = {row[0] for row in result}  # Set of used ports
    finally:
        connection.close()

    # Find the next available port in the range
    print("attempting to get next available port")
    for port in range(BASE_PORT, MAX_PORT + 1):
        if port not in used_ports:
            print("successfully located next available port. PORT:", port)
            return port

    raise RuntimeError("No available ports in the specified range.")

# Function to update the database with the assigned port
def update_port_in_db(user_id, assigned_port, row_id):
    try:
        connection = pymysql.connect(**db_config)
        with connection.cursor() as cursor:
            # Update the database with the assigned port
            sql = "UPDATE docker_containers SET port = %s WHERE ID = %s"
            cursor.execute(sql, (assigned_port, row_id))
            connection.commit()
            print(f"Database updated: ROW {row_id} assigned port {assigned_port}")
    except Exception as e:
        print(f"Failed to update the database with the port: {e}")
    finally:
        connection.close()

# Create a binlog stream reader
stream = BinLogStreamReader(
    connection_settings=db_config,
    server_id=101,  # Unique server ID, different from your MySQL server-id
    only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
    blocking=True,
    resume_stream=True
)

# Function to launch a Docker container using docker-compose
def launch_container(userID, challengeID, port, row_id):
    compose_file_path = f"/var/www/CyberCity/dockerStuff/{challengeID}/docker-compose.yml"
    env_file_path = f"/var/www/CyberCity/dockerStuff/{challengeID}/.env"

    with open(env_file_path, 'w') as f:
        f.write(f"PORT={port}\n")
        f.write(f"USER={userID}\n")

    print("attempting to launch container")

    try:
        # Use docker-compose to spin up the container with the specified port

        subprocess.run(
            ["sudo", "docker", "compose", "-p", str(port), "-f", compose_file_path, "up", "-d", "--build"],
            check=True,
	)
        print(f"Launched container for user {userID} with challenge ID {challengeID} on port {port}")

        # Store the creation time of the container
        timeInitialised = datetime.now()
        active_containers[row_id] = {
            "challenge_id": challengeID,
            "port": port,
            "creation_time": timeInitialised
        }
        print("successfully launched container")


    except Exception as e:
        print(f"Error while launching container for user {userID} with challenge ID {challengeID}: {e}")

# Function to remove a Docker container using docker-compose and delete the database entry
def remove_container(userID, challengeID, port, row_id):
    compose_file_path = f"/var/www/CyberCity/dockerStuff/{challengeID}/docker-compose.yml"


    print(f"Attempting to remove container on port: {port}")
    env_vars = os.environ.copy()
    env_vars["PORT"] = str(port)
    env_vars["USER"] = str(userID)

    try:
        # Remove the container and associated resources using docker-compose
        subprocess.run(["sudo", f"USER={userID}", f"PORT={port}", "docker", "compose", "-p", f"{port}", "down", "--volumes", "--remove-orphans"], check=True)
        # subprocess.run(["docker", "compose", "-p", str(port), "-f", compose_file_path, "down", "--volumes", "--remove-orphans"], check=True, env=env_vars)
        print(f"Completely removed container and resources for user {userID}, row ID {row_id} on challenge {challengeID} at port {port}")

        # Remove from active containers list
        active_containers.pop(row_id, None)

        print("Removed container")

        # Remove the database entry for this container
        try:
            connection = pymysql.connect(**db_config)
            with connection.cursor() as cursor:
                sql = "DELETE FROM docker_containers WHERE ID = %s AND challengeID = %s"
                cursor.execute(sql, (row_id, challengeID))
                connection.commit()
                print(f"Database entry removed for User ID {userID}, row ID {row_id} and Challenge ID {challengeID}")
        except Exception as db_error:
            print(f"Error while removing the database entry for user {userID}, row ID {row_id}: {db_error}")
        finally:
            connection.close()

    except Exception as e:
        print(f"Error while stopping container for user {userID}, row ID {row_id}: {e}")


# Function to handle binlog events and manage containers
def process_binlog_event():
    for binlogevent in stream:
        if binlogevent.table == "docker_containers":  # Adjust table name as needed
            for row in binlogevent.rows:
                # Handle different types of row events
                if isinstance(binlogevent, WriteRowsEvent):
                    data = row["values"]
                    event_type = "INSERT"
                elif isinstance(binlogevent, UpdateRowsEvent):
                    data = row["after_values"]
                    event_type = "UPDATE"
                elif isinstance(binlogevent, DeleteRowsEvent):
                    data = row["values"]
                    event_type = "DELETE"
                
                print(f"Debug: Row data = {data}, Event Type = {event_type}")

                # Retrieve necessary information from database row
                userID = data.get('UNKNOWN_COL2')  # Adjust column name
                challengeID = data.get('UNKNOWN_COL3')  # Adjust column name
                time_initialised = data.get('UNKNOWN_COL1')  # Get time from the event
                row_id = data.get('UNKNOWN_COL0')  # Get row ID

                if time_initialised:
                    # Convert the time_initialised to a datetime object
                    try:
                        creation_time = time_initialised
                        creation_time = creation_time + timedelta(hours=11)
                        print("database time is:", creation_time)
                        #creation_time = datetime.strptime(time_initialised, '%Y-%m-%d %H:%M:%S')
                        #creation_time = datetime.now()
                    except ValueError:
                        print(f"Error parsing timeInitialised for User ID {userID}")
                        continue

                    # Handle container creation on INSERT
                    if event_type == "INSERT":
                        port = get_next_available_port()
                        update_port_in_db(userID, port, row_id)
                        launch_container(userID, challengeID, port, row_id)

                        # Schedule the container removal
                        delete_time = creation_time + timedelta(minutes=20)
                        print("Container deletion time for user:", userID, "on row: ", row_id, "is:", delete_time)
                        active_containers[row_id] = {
                             "challengeID": challengeID,
                            "port": port,
                            "delete_time": delete_time
                        }

                    # Handle time extension on UPDATE
                    elif event_type == "UPDATE":
                        # Update the container's deletion time
                        delete_time = creation_time + timedelta(minutes=20)
                        if row_id in active_containers:
                            active_containers[row_id]["delete_time"] = delete_time
                            print(f"Updated deletion time for User ID {userID}, row {row_id} to {delete_time}")

                # Check for expired containers
                current_time = datetime.now()
                for userID, container_info in list(active_containers.items()):
                    delete_time = container_info["delete_time"]
                    if current_time > delete_time:
                        print("removing container with delete time of", delete_time, "because current time is:", current_time)
                        remove_container(userID, container_info["challengeID"], container_info["port"], row_id)

# Start the time tracker in a separate thread
time_thread = threading.Thread(target=time_tracker, daemon=True)
time_thread.start()

try:
    process_binlog_event()
except KeyboardInterrupt:
    print("Stopping the binlog monitoring.")
finally:
    stream.close()
