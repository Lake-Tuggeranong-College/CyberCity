

/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/



// Wifi & Webserver
#include "WiFi.h"
#include "sensitiveInformation.h"
#include <HTTPClient.h>

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

void setup() {
  /*
  */
  Serial.begin(9600);
  while (!Serial) {
    delay(10);
  }
  delay(1000);


  if (!tempsensor.begin()) {
    Serial.println("Couldn't find ADT7410!");
    while (1);
  }
  commonSetup();
}

void loop() {
  float sensorData = tempsensor.readTempC();
  updateEPD("Fire Dept", "Temp \tC", sensorData);
  String dataToPost = String(sensorData);
  uploadData(dataToPost, 30000);
  // waits 180 seconds (3 minutes) as per guidelines from adafruit.
  display.clearBuffer();

}
