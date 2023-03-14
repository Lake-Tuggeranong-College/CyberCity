/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!

  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/

#include "Adafruit_ThinkInk.h"
#include "WiFi.h"
#include "sensitiveInformation.h"
#include "RTClib.h"

RTC_PCF8523 rtc;

#define EPD_CS      15
#define EPD_DC      33
#define SRAM_CS     32
#define EPD_RESET   -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY    -1 // can set to -1 to not use a pin (will wait a fixed delay)

#include <Wire.h>
#include "Adafruit_ADT7410.h"
// Create the ADT7410 temperature sensor object
Adafruit_ADT7410 tempsensor = Adafruit_ADT7410();
// 1.54" Monochrome displays with 200x200 pixels and SSD1681 chipset
//ThinkInk_154_Mono_D67 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 1.54" Monochrome displays with 200x200 pixels and SSD1608 chipset
//ThinkInk_154_Mono_D27 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 1.54" Monochrome displays with 152x152 pixels and UC8151D chipset
//ThinkInk_154_Mono_M10 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);


// 2.13" Monochrome displays with 250x122 pixels and SSD1675 chipset
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 2.13" Monochrome displays with 250x122 pixels and SSD1675B chipset
//ThinkInk_213_Mono_B73 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 2.13" Monochrome displays with 250x122 pixels and SSD1680 chipset
//ThinkInk_213_Mono_BN display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);
//ThinkInk_213_Mono_B74 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 2.13" Monochrome displays with 212x104 pixels and UC8151D chipset
//ThinkInk_213_Mono_M21 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);


// 2.9" 4-level Grayscale (use mono) displays with 296x128 pixels and IL0373 chipset
//ThinkInk_290_Grayscale4_T5 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 2.9" Monochrome displays with 296x128 pixels and UC8151D chipset
//ThinkInk_290_Mono_M06 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);


// 4.2" Monochrome displays with 400x300 pixels and SSD1619 chipset
//ThinkInk_420_Mono_BN display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

// 4.2" Monochrome displays with 400x300 pixels and UC8276 chipset
//ThinkInk_420_Mono_M06 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);


void setup() {
  Serial.begin(115200);
  while (!Serial) { delay(10); }
  Serial.println("Adafruit EPD full update test in mono");
  display.begin(THINKINK_MONO);

  if (!tempsensor.begin()) {
    Serial.println("Couldn't find ADT7410!");
    while (1);
  }

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

  
  logEvent("System Initialisation...");
}


void loop() {
  Serial.println("Banner demo");
  display.clearBuffer();
  display.setTextSize(3);
  display.setCursor((display.width() - 180)/2, (display.height() - 24)/2);
  display.setTextColor(EPD_BLACK);
  display.print("Monochrome");
  display.display();

  delay(2000);
  
  Serial.println("B/W rectangle demo");
  display.clearBuffer();
  display.fillRect(display.width()/2, 0, display.width()/2, display.height(), EPD_BLACK);
  display.display();

  delay(2000);
  
  Serial.println("Text demo");
  // large block of text
  display.clearBuffer();
  display.setTextSize(1);
  testdrawtext("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur adipiscing ante sed nibh tincidunt feugiat. Maecenas enim massa, fringilla sed malesuada et, malesuada sit amet turpis. Sed porttitor neque ut ante pretium vitae malesuada nunc bibendum. Nullam aliquet ultrices massa eu hendrerit. Ut sed nisi lorem. In vestibulum purus a tortor imperdiet posuere. ", EPD_BLACK);
  display.display();

  delay(2000);

  display.clearBuffer();
  for (int16_t i=0; i<display.width(); i+=4) {
    display.drawLine(0, 0, i, display.height()-1, EPD_BLACK);
  }

  for (int16_t i=0; i<display.height(); i+=4) {
    display.drawLine(display.width()-1, 0, 0, i, EPD_BLACK);
  }
  display.display();

  delay(2000);  
}


void testdrawtext(const char *text, uint16_t color) {
  display.setCursor(0, 0);
  display.setTextColor(color);
  display.setTextWrap(true);
  display.print(text);
}

void updateEPD() {

  // Indigenous Country Name
  drawText("Namadgi", EPD_BLACK, 2, 0, 0);


  // Config
  drawText(WiFi.localIP().toString(), EPD_BLACK, 1, 130, 80);
  drawText(getTimeAsString(), EPD_BLACK, 1, 130, 100);
  drawText(getDateAsString(), EPD_BLACK, 1, 130, 110);


  // Draw lines to divvy up the EPD
  display.drawLine(0, 20, 250, 20, EPD_BLACK);
  display.drawLine(125, 20, 125, 122, EPD_BLACK);
  display.drawLine(0, 75, 250, 75, EPD_BLACK);

  


  drawText("Temp \tC", EPD_BLACK, 2, 0, 80);
  drawText(String(tempsensor.readTempC()), EPD_BLACK, 4, 0, 95);

  logEvent("Updating the EPD");
  display.display();

}


void logEvent(String dataToLog) {
  /*
     Log entries to a file stored in SPIFFS partition on the ESP32.
  */
  // Get the updated/current time
  DateTime rightNow = rtc.now();
  char csvReadableDate[25];
  sprintf(csvReadableDate, "%02d,%02d,%02d,%02d,%02d,%02d,",  rightNow.year(), rightNow.month(), rightNow.day(), rightNow.hour(), rightNow.minute(), rightNow.second());

  String logTemp = csvReadableDate + dataToLog + "\n"; // Add the data to log onto the end of the date/time

  const char * logEntry = logTemp.c_str(); //convert the logtemp to a char * variable

  //Add the log entry to the end of logevents.csv

  // Output the logEvents - FOR DEBUG ONLY. Comment out to avoid spamming the serial monitor.
  //  readFile(SPIFFS, "/logEvents.csv");

  Serial.print("\nEvent Logged: ");
  Serial.println(logEntry);
}
