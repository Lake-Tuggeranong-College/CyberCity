// Keep only LCD-related functionality for the Mini TFT Wing + ST7735 display

#include <Wire.h>

#include <Adafruit_GFX.h>    // Core graphics library
#include <Adafruit_ST7735.h> // Hardware-specific library for ST7735
#include "Adafruit_miniTFTWing.h"

Adafruit_miniTFTWing ss;
#define TFT_RST    -1     // we use the seesaw for resetting to save a pin
#define TFT_CS     14     // Custom CS pin
#define TFT_DC     32     // Custom DC pin
Adafruit_ST7735 tft = Adafruit_ST7735(TFT_CS, TFT_DC, TFT_RST);

void setup() {
  Serial.begin(9600);
  while (!Serial) { delay(10); }

  if (!ss.begin()) {
    Serial.println("seesaw init error!");
    while (1) { delay(10); }
  }
  Serial.println("seesaw started");

  ss.tftReset();
  ss.setBacklight(0x0); // set the backlight fully on

  // Initialize a ST7735S mini 160x80 display
  tft.initR(INITR_MINI160x80);
  tft.setRotation(1);
  tft.fillScreen(ST77XX_BLACK);

  // Draw a simple boot message
  tft.setCursor(0, 0);
  tft.setTextSize(2);
  tft.setTextColor(ST77XX_WHITE);
  tft.setTextWrap(true);
  tft.print("LCD Ready");
}

void loop() {
  // Nothing to do here for now; LCD helper functions can be called as needed.
  delay(100);
}

// Helper to draw text on the TFT
void tftDrawText(String text, uint16_t color) {
  tft.fillScreen(ST77XX_BLACK);
  tft.setCursor(0, 0);
  tft.setTextSize(2);
  tft.setTextColor(color);
  tft.setTextWrap(true);
  tft.print(text);
}