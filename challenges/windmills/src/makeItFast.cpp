#include <ArduinoJson.h>
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
#include <WiFi.h>
#include <HTTPClient.h>
CyberCitySharedFunctionality cyberCity;

#include <ESP32Servo.h>

const char* server = "192.168.1.10";


#define servoPin 21 // Declare the Servo pin
#define AIN_PIN A2  // Arduino pin that connects to AOUT pin of moisture sensor


// Create a servo object
Servo Servo1;
String outputCommand = "NaN";

String readFromDatabase(const char* server, const char* table, const char* column, const char* where) {
    if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    String url = String(server) + "/Cybercity/website/esp32Test/esp32Read.php?table=" + table + "&column=" + column + "&where=" + where;
    http.begin(url.c_str());

    int httpResponseCode = http.GET();
    String payload = "";

    if(httpResponseCode > 0) {
      payload = http.getString();
    } else {
      Serial.print("Error on HTTP request: ");
      Serial.println(httpResponseCode);
    }

    http.end();
    return payload;

    } else {
      Serial.println("Wi-Fi not connected");
      return "";
    }
}

bool writeToDatabase(const char* server, const char* table, const char* column, const char* value, const char* where) {
    if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    String url = String(server) + "/Cybercity/website/esp32Test/esp32Write.php";

    http.begin(url.c_str());
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "table=" + String(table) + "&column" + String(column) + "&value=" + String(value) + "&where=" + String(where);

    int httpResponseCode = http.POST(postData);
    http.end();

    return (httpResponseCode > 0);
    } else {
      Serial.println("Wi-Fi not connected");
      return false;
    }
}


void setup() {
  Serial.begin(9600); //Initialisation
  setCpuFrequencyMhz(240);
  
  Servo1.setPeriodHertz(50);  // standard 50 hz servo
  Servo1.attach(servoPin);

  while (!Serial) {
    delay(10);
  }
  delay(1000);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  Serial.println();
  Serial.print("Connected to the Internet");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  pinMode(LED_BUILTIN, OUTPUT);

  Serial.println("Server connect test: ");
  String data = readFromDatabase(server, "RegisteredModules", "CurrentOutput", "id=46");

  Serial.println("Read data: " + data);

  bool success = writeToDatabase(server, "RegisteredModules", "CurrentOutput", "Off", "ud=46");
  if(success) {
    Serial.println("Write sucessful");

  } else {
    Serial.println("Write failed");
  }


  // RTC
  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
    Serial.flush();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  rtc.start();

  //EINK
  display.begin();
  display.clearBuffer();

  cyberCity.logEvent("System Initialisation...");
}



void loop() {
  
  }
  


  /*
  int value = analogRead(AIN_PIN);  // read the analog value from sensor

  Serial.println(value);
  int sensorData = value * 1.0;
  cyberCity.updateEPD("Farm", "value", sensorData, outputCommand);
  String dataToPost = String(value);
  // cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 40, serverName, true, true);
  //notes need to // the next to line 
//  int payloadLocation = payload.indexOf("Payload:");
 //char serverCommand = payload.charAt(payloadLocation + 8);

 Serial.print("Payload from server:");
  Serial.println(payload);
  DynamicJsonDocument doc(1024);
//  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    return;
  }
  const char* command = doc["command"];
  Serial.print("Command: ");
  Serial.println(command);
  
  
  if (String(command) == "On") {
    Serial.println("spin:)");
    Servo1.write(0);
   outputCommand = "Fan On";
    
 } else {
   outputCommand = "Fan Off";
    Servo1.write(90);
  }
}*/
 
 
  //Serial.print("Command: ");
 //Serial.print(Command);
 //if (serverCommand == "on0") {
   //outputCommand = "Fan On";
   // Servo1.write(0);
 //} else {
  // /outputCommand = "Fan Off";
   // Servo1.write(90);
 // }
  //display.clearBuffer();

  //delay(500);
//}
/*
if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);

    int httpResponsecode = http.GET();

    if(httpResponsecode > 0) {
      String payload = http.getString();
      Serial.println(payload);
    } else {
      Serial.print("HTTP Request Error");
      Serial.println(httpResponsecode);
    }
    http.end();
    } else {
      Serial.println("Wi-Fi not connected");
    
    }
    delay(50);
*/