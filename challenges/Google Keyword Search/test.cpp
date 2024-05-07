// Core libraries.
#include <Arduino.h>
#include <Adafruit_GFX.h>    // Core graphics library
#include <Adafruit_ST7735.h> // Hardware-specific library for ST7735
#include <Adafruit_ST7789.h>
#include "Adafruit_miniTFTWing.h"

// 2-way connection between modules and website libraries.
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "sensitiveInformation.h"

Adafruit_miniTFTWing ss;
#define TFT_RST    -1    // we use the seesaw for resetting to save a pin

#if defined(ESP32) && !defined(ARDUINO_ADAFRUIT_FEATHER_ESP32S2)
  #define TFT_CS   14
  #define TFT_DC   32
#endif

Adafruit_ST7789 tft_7789 = Adafruit_ST7789(TFT_CS,  TFT_DC, TFT_RST);
Adafruit_ST7735 tft_7735 = Adafruit_ST7735(TFT_CS, TFT_DC, TFT_RST);

// we'll assign it later
Adafruit_ST77xx *tft = NULL;
uint32_t version;

String outputCommand = "NaN";

void textDisplay() {
    uint32_t buttons = ss.readButtons();
  //Serial.println(buttons, BIN);
  
  uint16_t color;

  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_LEFT)) {
    Serial.println("LEFT");
    color = ST77XX_WHITE;
  }
  if (version == 3322) {
    delay(500);
    tft->fillTriangle(200, 45, 200, 85, 220, 65, color);
  } else {
    delay(500);
    tft->fillTriangle(150, 30, 150, 50, 160, 40, color);
  } 

  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_RIGHT)) {
    delay(1000);
    Serial.println("RIGHT");
    color = ST77XX_WHITE;
  }
  if (version == 3322) {
    tft->fillTriangle(120, 45, 120, 85, 100, 65, color);
  } else {
    tft->fillTriangle(120, 30, 120, 50, 110, 40, color);
  } 

  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_DOWN)) {
    delay(1000);
    Serial.println("DOWN");
    color = ST77XX_WHITE;
  }
  if (version == 3322) {
    tft->fillTriangle(140, 25, 180, 25, 160, 10, color);
  } else {
    tft->fillTriangle(125, 26, 145, 26, 135, 16, color);
  }
  
  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_UP)) {
    delay(1000);
    Serial.println("UP");
    color = ST77XX_WHITE;
  }
  if (version == 3322) {
    tft->fillTriangle(140, 100, 180, 100, 160, 120, color);
  } else {
    tft->fillTriangle(125, 53, 145, 53, 135, 63, color);
  }
  
  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_A)) {
    delay(1000);
    Serial.println("A");
    color = ST7735_GREEN;
  }
  if (version == 3322) {
    tft->fillCircle(40, 100, 20, color);
  } else {
    tft->fillCircle(30, 57, 10, color);
  }

  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_B)) {
    delay(1000);
    Serial.println("B");
    color = ST77XX_YELLOW;
  }
  if (version == 3322) {
    tft->fillCircle(40, 30, 20, color);
  } else {
    tft->fillCircle(30, 18, 10, color);
  }
  
  color = ST77XX_BLACK;
  if (! (buttons & TFTWING_BUTTON_SELECT)) {
    delay(1000);
    Serial.println("SELECT");
    color = ST77XX_RED;
  }
  if (version == 3322) {
    tft->fillCircle(160, 65, 20, color);
  } else {
    tft->fillCircle(80, 40, 7, color);
  }
}

String websiteTextDisplay = "Hello World";

// Check if it's time to send the data
unsigned long currentMillis = millis();
unsigned long previousMillis = 0;
const long interval = 180000;

void dataTransfer() {
  // Check if it's time to send the data.
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
  }

  // Create a JSON object
  JsonDocument doc;
  Serial.print("Debug JSON String: ");  
  doc["module_value"] = websiteTextDisplay;
  doc["module_name"] = sensorName;
  doc["module_locate"] = sensorLocation;

  // Create an HTTP client and send the JSON string to the server.
  HTTPClient http;
  http.begin(serverName);
  http.addHeader("Content-Type", "application/json");

  // Serialize JSON to send as a string.
  String serverOutputJSON;
  serializeJson(doc, serverOutputJSON);

  // Send the JSON string as the payload
  int httpResponseCode = http.POST(serverOutputJSON);
  Serial.println(serverOutputJSON);

  // HTTP response code type (200 == OK, >400 or >500 == something related to website/server).
  if (httpResponseCode > 0)
  {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
  }
  else
  {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  // Free resources.
  http.end();

  // Add a delay to prevent flooding the server with requests.
  delay(1000);
}

void setup() {
  Serial.begin(9600);
  while (!Serial) {
    delay(10); // Wait until serial console is opened.
  }

  delay(1000);
  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi!");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  if (!ss.begin()) {
    Serial.println("seesaw not found!");
    while(1);
  } else {
    Serial.println("seesaw found!");
  }

  version = ((ss.getVersion() >> 16) & 0xFFFF);
  Serial.print("Seesaw Version: "); 
  Serial.println(version);
  if (version == 3322) {
    Serial.println("Version 2 TFT FeatherWing found");  
  } else {
    Serial.println("Version 1 TFT FeatherWing found");
  }

  Serial.println("Reset module to the latest code...");
  ss.tftReset();   // reset the display
  Serial.println("Backlight is tunred off.");
  ss.setBacklight(0x0000);  // turn off the backlight

  if (version == 3322) {
    tft_7789.init(135, 240);
    tft = &tft_7789;
  } else {
    tft_7735.initR(INITR_MINI160x80);   // initialize a ST7735S chip, mini display
    tft = &tft_7735;
  }

  tft->setRotation(1);
  Serial.println("TFT initialized");

  Serial.println("Filling the screen with red color...");
  tft->fillScreen(ST77XX_RED);
  delay(1000);
  Serial.println("Filling the screen with green color...");
  tft->fillScreen(ST77XX_GREEN);
  delay(1000);
  Serial.println("Filling the screen with blue color...");
  tft->fillScreen(ST77XX_BLUE);
  delay(1000);

  Serial.println("Filling the screen with black color...");
  tft->fillScreen(ST77XX_BLACK);
  delay(1000);
  Serial.println("Setting text color...");
  tft->setTextColor(ST77XX_WHITE, ST77XX_BLACK);
  delay(1000);
  Serial.println("Setting text size...");
  tft->setTextSize(1);
  delay(1000);
  Serial.println("Setting cursor position...");
  tft->setCursor(0, 0);
  delay(1000);
  Serial.println("Printing to display...");
  tft->print(websiteTextDisplay);
}

void loop() {
  textDisplay();
  delay(10);

  dataTransfer();
}