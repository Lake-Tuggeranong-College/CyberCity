// version 1.0.0 28.03.2023 - JR
// version 1.1.0 24.04.2023 - RC
// v2.0.0 16.05.2023 - RC

#ifndef CyberCitySharedFuntionality_H
#define CyberCitySharedFuntionality_H
#include <Arduino.h>

#include "WiFi.h"
#include <HTTPClient.h>

#include "Adafruit_ThinkInk.h"

#define EPD_CS      15
#define EPD_DC      33
#define SRAM_CS     32
#define EPD_RESET   -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY    -1 // can set to -1 to not use a pin (will wait a fixed delay)

// 2.13" Monochrome displays with 250x122 pixels and SSD1675 chipset
extern ThinkInk_213_Mono_B72 display;

// RTC
#include "RTClib.h"

extern RTC_PCF8523 rtc;

class CyberCitySharedFuntionality
{
private:
    String getDateAsString();
    String getTimeAsString();

public:
    CyberCitySharedFuntionality();
    void commonSetup();
    void updateEPD(String title, String dataTitle, float dataToDisplay, String outputCommand);
    void drawText(String text, uint16_t color, int textSize, int x, int y);

    void logEvent(String dataToLog);
    String dataTransfer(String dataToPost, String apiKeyValue, String sensorName, String sensorLocation, int delayBetweenPosts, String serverName, boolean postData, boolean readData);
};
#endif
