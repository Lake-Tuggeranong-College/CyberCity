from pymysqlreplication import BinLogStreamReader
from pymysqlreplication.row_event import WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent
import paho.mqtt.client as mqtt
import time

# MySQL connection settings
db_config = {
    'host': 'localhost',
    'user': 'LTC',
    'passwd': 'LTCpcgame5',
    'port': 3306
}

# MQTT configuration
mqtt_broker = "localhost"
mqtt_port = 1883

# Set up MQTT client with debugging
client = mqtt.Client()

# MQTT callback functions for debugging
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("MQTT: Connected successfully")
    else:
        print(f"MQTT: Connection failed with code {rc}")

def on_publish(client, userdata, mid):
    print(f"MQTT: Message {mid} published")

def on_disconnect(client, userdata, rc):
    if rc != 0:
        print(f"MQTT: Unexpected disconnection (code {rc})")

# Assign callback functions
client.on_connect = on_connect
client.on_publish = on_publish
client.on_disconnect = on_disconnect

# Connect to MQTT broker
try:
    print("Connecting to MQTT broker...")
    client.connect(mqtt_broker, mqtt_port, 60)
    print("MQTT: Connection attempt made")
except Exception as e:
    print(f"MQTT: Connection failed with exception: {e}")

# Start the network loop to process callbacks
client.loop_start()

# Publishing a test message to verify the connection.
try:
    test_topic = "test/connection"
    test_message = "Hello, MQTT!"
    result = client.publish(test_topic, test_message)
    if result.rc != mqtt.MQTT_ERR_SUCCESS:
        print(f"MQTT: Failed to publish test message. Error code: {result.rc}")
    else:
        print(f"Published test message '{test_message}' to topic '{test_topic}'")
except Exception as e:
    print(f"MQTT: Error while publishing test message: {e}")

# Create a binlog stream reader
stream = BinLogStreamReader(
    connection_settings=db_config,
    server_id=100,  # Unique server ID, different from your MySQL server-id
    only_events=[WriteRowsEvent, UpdateRowsEvent, DeleteRowsEvent],
    blocking=True,
    resume_stream=True
)

# Function to handle binlog events and publish to MQTT
def process_binlog_event():
    for binlogevent in stream:
        # Check if the event is related to the 'RegisteredModules' table
        if binlogevent.table == "Challenges":
            for row in binlogevent.rows:
                # Handle different types of row events
                if isinstance(binlogevent, WriteRowsEvent):
                    data = row["values"]
                elif isinstance(binlogevent, UpdateRowsEvent):
                    data = row["after_values"]
                elif isinstance(binlogevent, DeleteRowsEvent):
                    data = row["values"]

                # Debugging: Print out the data dictionary
                print(f"Debug: Row data = {data}")

                # Ensure the keys match exactly to what's in your database schema
                topic = data.get('UNKNOWN_COL5')
                message = data.get('UNKNOWN_COL6')

                # Check if 'Module' and 'CurrentOutput' columns exist in the data
                if topic is None:
                    print("Warning: 'Module' column is not found or has no data.")
                    topic = "default_topic"
                if message is None:
                    print("Warning: 'CurrentOutput' column is not found or has no data.")
                    message = "No data"

                # Construct MQTT topic and message
                topic = f"Challenges/{topic}"
                
                # Publish the message to the topic
                client.publish(topic, message, retain=True)
                print(f"Published '{message}' to topic '{topic}'")

try:
    process_binlog_event()
except KeyboardInterrupt:
    print("Stopping the binlog monitoring.")
finally:
    stream.close()
    client.disconnect()
