/*
 * Contains any sensitive Infomration that you do not want published to Github.
 * 
 * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home). * The SSID and Password variables will need to be changed if you’re connecting to another Wireless Access Point (such as at home).

 * The `http_username` and `http_password` variables are used to authenticate when users are attempting to access secured pages.
 * 
 * Make sure this file is included in the .gitignore!
 * 
 */

const char* host = "RMS";
const char* ssid = "RoboRange";       // Wifi Network Name
const char* password = "Password01";  // Wifi Password
// const char* ssid = "gogogadgetnbn";       // Wifi Network Name
// const char* password = "st@rw@rs";  // Wifi Password

String serverURL = "http://192.168.1.10/JEDI2023/dataTransfer.php";
String eventLogURL = "http://192.168.1.10/JEDI2023/eventLog.php";


String apiKeyValue = "password";
String moduleName = "onboardLED";
String userName = "Ryan Cather";


