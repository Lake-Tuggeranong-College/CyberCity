#include <CyberCitySharedFunctionality.h>
#include <ArduinoJson.h>
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/
 int green = 15;
 int red = 16;
 int yellow = 17;

#include "sensitiveInformation.h"

CyberCitySharedFunctionality cyberCity;




void setup() {
  /*
  */
  Serial.begin(9600);
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

  // Module Specific Code

  // put your setup code here, to run once:
  pinMode(green, INPUT);
  pinMode(red, OUTPUT);
  pinMode(yellow, OUTPUT);
  Serial.begin(9600);
}

void loop() {


  // put your main code here, to run repeatedly:
  lights();
  int sensorData = red, green, yellow;
  String dataToPost = String(sensorData);
  // cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName, true, true);    
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
  Serial.print(command);
  delay(500);
}

void lights() {
 digitalWrite(red, HIGH);
 delay(15000);
digitalWrite(red,  LOW);
  
  digitalWrite(yellow, HIGH);
delay(1000);
  digitalWrite(yellow,  LOW);
delay(500);

  digitalWrite(yellow, HIGH);
delay(1000);
  digitalWrite(yellow,  LOW);
delay(500);


  
digitalWrite(green, HIGH);
delay(20000);
digitalWrite(green,  LOW);

}
