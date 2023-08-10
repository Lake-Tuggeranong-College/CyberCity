
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/


#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;
//Temperature Sensor
#include <Wire.h>
#include <WiFi.h>
#include <RTClib.h>
#include "Adafruit_ADT7410.h"
// Create the ADT7410 temperature sensor object
Adafruit_ADT7410 tempsensor = Adafruit_ADT7410();
//#define display
//#define clear
#define pizopin 14

//RTC_DS3231 rtc;

String outputCommand = "NaN";
void setup() {

pinMode(pizopin,OUTPUT);
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
    //    abort();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.begin();

  //EINK
  display.begin();
  display.clearBuffer();

  cyberCity.logEvent("System Initialisation...");

  if (!tempsensor.begin()) {
    Serial.println("Couldn't find ADT7410!");
    while (1)
      ;
  }
}

void loop() {
  
  float sensorData = tempsensor.readTempC();
  cyberCity.updateEPD("Fire Dept", "Temp \tC", sensorData, outputCommand);
  String dataToPost = String(sensorData);
  // cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 60000, serverName, true, true);
  int payloadLocation = payload.indexOf("Payload:");
  char serverCommand = payload.charAt(payloadLocation + 8);
  Serial.print("Command: ");
  Serial.print(payload);
  if (serverCommand == '1') {
    tone(pizopin,500,1000);
    outputCommand = "LED On";
    digitalWrite(LED_BUILTIN, HIGH);
  } else {
    noTone(pizopin);
    outputCommand = "LED Off";
    digitalWrite(LED_BUILTIN, LOW);
  }
  // waits 180 seconds (3 minutes) as per guidelines from adafruit.
  display.clearBuffer();
}
