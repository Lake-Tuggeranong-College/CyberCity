// Core libraries.
#include <Arduino.h>
#include <Adafruit_GFX.h>    // Core graphics library
#include <Adafruit_ST7735.h> // Hardware-specific library for ST7735
#include <Wire.h>
#include <Adafruit_Seesaw.h>

Adafruit_seesaw ss;

// Custom Libraries
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;

// For the Adafruit Mini TFT with Joystick Featherwing, these are the default pins:
#define TFT_CS     15
#define TFT_RST    -1  // Can use for a reset command
#define TFT_DC     33

// Create an instance of the display:
Adafruit_ST7735 tft = Adafruit_ST7735(TFT_CS, TFT_DC, TFT_RST);

void setup() {
  Serial.begin(115200);
  while (!Serial) {
    ; // Wait for serial port to connect. Needed for native USB port only
  }
  
  if (!ss.begin(0x5E)) {
    Serial.println("seesaw not found!");
    while(1);
  } else {
    Serial.println("seesaw found!");
  }

  // Set the backlight to the lowest brightness (0 = off)
  Serial.println("Backlight dimmed to 0%.");
  ss.analogWrite(5, 0); // Ensure this is the correct pin for backlight control

  Serial.println("Initializing display...");
  tft.initR(INITR_MINI160x80);  // Init ST7735S chip, mini display
  delay(500); // Add a delay to allow the display to stabilize
  Serial.println("Display initialized successfully.");

  // Additional debug statements
  Serial.println("Filling screen...");
  tft.fillScreen(ST77XX_BLACK);
  Serial.println("Setting text color...");
  tft.setTextColor(ST77XX_WHITE, ST77XX_BLACK);
  Serial.println("Setting text size...");
  tft.setTextSize(1);
  Serial.println("Setting cursor position...");
  tft.setCursor(0, 0);
  Serial.println("Printing to display...");
  tft.print("Hello World");

  Serial.println("Setup complete.");
}

void loop() {
  // Nothing to do here
}