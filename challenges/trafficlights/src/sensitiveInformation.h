/*
 * Contains any sensitive Infomration that you do not want published to Github.
 * 
 * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home).
 * The `http_username` and `http_password` variables are used to authenticate when users are attempting to access secured pages.
 * 
 * Make sure this file is included in the .gitignore!
 * 
 */

const char* host            = "RMSh";
const char* ssid            = "CyberRange";        // Wifi Network Name
const char* password        = "CyberRange";       // Wifi Password

//const char* serverName = "http://192.168.1.106/espPost/post-esp-data.php";
 //const char* serverName = "http://192.168.1.18/postESPData.php";
//String serverName = "http://192.168.1.10/CyberCity/website/esp32/dataTransfer.php";

String apiKeyValue = "IcFxFF";

String sensorName = "TrafficLight";

String sensorLocation = "TrafficLightOne";

// MQTT client name
const char* mqttClient = "ESP323";

// MQTT Topic
const char* mqttTopic = "RegisteredModules/TrafficLight"; // It's worth noting that an ESP32 can subscribe to more than 1 topic

// Replace with the MQTT broker IP address and port (default port for MQTT is 1883)
const char* mqttServer = "198.168.1.10";  
const int mqttPort = 1883;
