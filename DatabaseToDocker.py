from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import docker
import time
from datetime import datetime, timedelta
import pymysql
import os
import subprocess

# MySQL connection settings
db_config = {
    'host': '10.177.200.71',
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
            print("successfully located next available port")
            return port

    raise RuntimeError("No available ports in the specified range.")

# Function to update the database with the assigned port
def update_port_in_db(user_id, assigned_port):
    try:
        connection = pymysql.connect(**db_config)
        with connection.cursor() as cursor:
            # Update the database with the assigned port
            sql = "UPDATE docker_containers SET port = %s WHERE userID = %s"
            cursor.execute(sql, (assigned_port, user_id))
            connection.commit()
            print(f"Database updated: User ID {user_id} assigned port {assigned_port}")
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
def launch_container(userID, challengeID, port):
    compose_file_path = f"./dockerStuff/{challengeID}/docker-compose.yml"

    print("attempting to launch container")
    
    try:
        # Use docker-compose to spin up the container with the specified port
        subprocess.run([
            "docker-compose", 
            "-f", compose_file_path, 
            "up", "-d", 
            "--build", 
            f"--env", f"PORT={port}"
        ], check=True)
        
        print(f"Launched container for user {userID} with challenge ID {challengeID} on port {port}")

        # Store the creation time of the container
        timeInitialised = datetime.now()
        active_containers[userID] = {
            "challenge_id": challengeID,
            "port": port,
            "creation_time": timeInitialised
        }
        print("successfully launched container")


    except Exception as e:
        print(f"Error while launching container for user {userID} with challenge ID {challengeID}: {e}")

# Function to remove a Docker container using docker-compose and delete the database entry
def remove_container(userID, challengeID, port):
    compose_file_path = f"./dockerStuff/{challengeID}/docker-compose.yml"

    print("Attempting to remove container")

    try:
        # Remove the container and associated resources using docker-compose
        subprocess.run(["docker-compose", "-f", compose_file_path, "down", "--volumes", "--remove-orphans"], check=True)
        print(f"Completely removed container and resources for user {userID} on challenge {challengeID} at port {port}")

        # Remove from active containers list
        active_containers.pop(userID, None)

        print("Removed container")

        # Remove the database entry for this container
        try:
            connection = pymysql.connect(**db_config)
            with connection.cursor() as cursor:
                sql = "DELETE FROM docker_containers WHERE userID = %s AND challengeID = %s"
                cursor.execute(sql, (userID, challengeID))
                connection.commit()
                print(f"Database entry removed for User ID {userID} and Challenge ID {challengeID}")
        except Exception as db_error:
            print(f"Error while removing the database entry for user {userID}: {db_error}")
        finally:
            connection.close()

    except Exception as e:
        print(f"Error while stopping container for user {userID}: {e}")


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

                if time_initialised:
                    # Convert the time_initialised to a datetime object
                    try:
                        creation_time = datetime.strptime(time_initialised, '%Y-%m-%d %H:%M:%S')
                    except ValueError:
                        print(f"Error parsing timeInitialised for User ID {userID}")
                        continue

                    # Handle container creation on INSERT
                    if event_type == "INSERT":
                        port = get_next_available_port()
                        update_port_in_db(userID, port)
                        launch_container(userID, challengeID, port)

                        # Schedule the container removal
                        delete_time = creation_time + timedelta(minutes=20)
                        active_containers[userID] = {
                            "challengeID": challengeID,
                            "port": port,
                            "delete_time": delete_time
                        }

                    # Handle time extension on UPDATE
                    elif event_type == "UPDATE":
                        # Update the container's deletion time
                        delete_time = creation_time + timedelta(minutes=20)
                        if userID in active_containers:
                            active_containers[userID]["delete_time"] = delete_time
                            print(f"Updated deletion time for User ID {userID} to {delete_time}")

                # Check for expired containers
                current_time = datetime.now()
                for userID, container_info in list(active_containers.items()):
                    delete_time = container_info["delete_time"]
                    if current_time > delete_time:
                        remove_container(userID, container_info["challengeID"], container_info["port"])

try:
    process_binlog_event()
except KeyboardInterrupt:
    print("Stopping the binlog monitoring.")
finally:
    stream.close()