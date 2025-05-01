/*
 * Contains any sensitive Infomration that you do not want published to Github.
 * 
 * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home).
 *
 * This file is supposed to be in the .gitignore
 * 
 */


// Wifi network
const char* ssid = "CyberRange";       // Wifi Network Name
const char* password = "CyberRange";  // Wifi Password

// MQTT client name
const char* mqttClient = "ESP32";

// MQTT Topic
const char* mqttTopic = "Challenges/Two"; // It's worth noting that an ESP32 can subscribe to more than 1 topic

// Replace with the MQTT broker IP address and port (default port for MQTT is 1883)
const char* mqttServer = "192.168.1.10";  
const int mqttPort = 1883;
