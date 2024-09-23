/*
 * Contains any sensitive Infomration that you do not want published to Github.
 * 
 * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home). * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home).

 * The `http_username` and `http_password` variables are used to authenticate when users are attempting to access secured pages.
 * 
 * Make sure this file is included in the .gitignore!
 * 
 */


//Wifi network
const char* ssid = "CyberRange";       // Wifi Network Name
const char* password = "CyberRange";  // Wifi Password

//MQTT client name
const char* mqttClient = "receiverESP32";
// Replace with the MQTT broker IP address and port (default port for MQTT is 1883)
const char* mqttServer = "192.168.1.10";  
const int mqttPort = 1883;
