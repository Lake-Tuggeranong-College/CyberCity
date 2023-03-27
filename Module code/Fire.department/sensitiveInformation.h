/*
 * Contains any sensitive Infomration that you do not want published to Github.
 * 
 * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home).
 * The `http_username` and `http_password` variables are used to authenticate when users are attempting to access secured pages.
 * 
 * Make sure this file is included in the .gitignore!
 * 
 */

const char* host            = "RMS";
const char* ssid            = "RoboRange";        // Wifi Network Name
const char* password        = "Password01";       // Wifi Password

//const char* serverName = "http://192.168.1.106/espPost/post-esp-data.php";
 const char* serverName = "http://192.168.1.18/post-esp-data.php";

String apiKeyValue = "1";

String sensorName = "Fire Department";
String sensorLocation = "Fire Department";
