#include <CyberCitySharedFuntionality.h>

/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/


#include "sensitiveInformation.h"

CyberCitySharedFuntionality cyberCity;




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
  pinMode(14, INPUT);
  pinMode(32, OUTPUT);
  pinMode(33, OUTPUT);
  Serial.begin(9600);
}

void loop() {

  // put your main code here, to run repeatedly:
  Serial.println(digitalRead(14));
  lights();
  int sensorData = 97654;
  String dataToPost = String(sensorData);
  cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  // waits 180 seconds (3 minutes) as per guidelines from adafruit.
  display.clearBuffer();
}

void lights() {
  if (digitalRead(14)) {
    digitalWrite(32, HIGH);
    digitalWrite(33, LOW);
  }
  else {
    digitalWrite(32, LOW);
    digitalWrite(33, HIGH);
  }
}