#include "sensitiveInformation.h"

#include <CyberCityShareFuntionality.h>





/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/



// Wifi & Webserver
#include "WiFi.h"
#include <HTTPClient.h>

//test

// RTC
#include "RTClib.h"

RTC_PCF8523 rtc;

// EINK
#include "Adafruit_ThinkInk.h"

#define EPD_CS      15
#define EPD_DC      33
#define SRAM_CS     32
#define EPD_RESET   -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY    -1 // can set to -1 to not use a pin (will wait a fixed delay)

// 2.13" Monochrome displays with 250x122 pixels and SSD1675 chipset
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);


//Temperature Sensor
#include <Wire.h>
#include "Adafruit_ADT7410.h"
// Create the ADT7410 temperature sensor object
Adafruit_ADT7410 tempsensor = Adafruit_ADT7410();

CyberCityShareFuntionality cyberCity;

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
  if (! rtc.begin()) {
    Serial.println("Couldn't find RTC");
    Serial.flush();
    //    abort();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.start();

  //EINK
  display.begin();
  display.clearBuffer();


  cyberCity.logEvent("System Initialisation...");

  if (!tempsensor.begin()) {
    Serial.println("Couldn't find ADT7410!");
    while (1);
  }
}

void loop() {
  float sensorData = tempsensor.readTempC();
  cyberCity.updateEPD("Fire Dept", "Temp \tC", sensorData);
  String dataToPost = String(sensorData);
  cyberCity.uploadData(dataToPost, 30000);
  // waits 180 seconds (3 minutes) as per guidelines from adafruit.
  display.clearBuffer();

}
