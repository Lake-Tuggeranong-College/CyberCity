# MQTT

**What is MQTT?**

It's a lightweight communication protocol, meaning it's designed to work even if the devices have limited power, memory, or internet bandwidth. It's often used in the Internet of Things (IoT), where smart devices (like your phone, fridge, or even cars) need to talk to each other.

**Key Terms in MQTT:**

**Broker**: This is like a post office. It handles all the messages being sent and makes sure they go to the right devices. Every MQTT system has one broker.

**Client**: These are the devices that either send or receive messages. They can be anything from a temperature sensor to a mobile app.

**Publish**: When a client wants to share information (like a sensor sending temperature data), it "publishes" that data to the broker. It's like mailing a letter to the post office.

**Subscribe**: If a client wants to receive certain information (like an app wanting to know the current temperature), it "subscribes" to that topic. This is like signing up for a magazine subscription!

**Topic**: This is the label for the message. For example, a sensor might publish data under the topic "kitchen/temperature." Think of it like the address on a letter.

**QoS (Quality of Service)**: This describes how important the message delivery is. Higher QoS means more effort is made to ensure the message arrives, even if the connection is unreliable.

**Payload**: The actual content of the message—like the temperature value or a notification. It's what gets delivered!

## Example
Imagine you have a smart thermostat and a smartphone app:

The thermostat **publishes** the current temperature under the **topic** "house/livingroom/temperature."

Your app **subscribes** to that topic to get updates.

The **broker** ensures the message (temperature reading) gets sent to your app.

The **payload** might say "21°C," which your app displays.