// Keep only LCD-related functionality for the Mini TFT Wing + ST7735 display

#include <Wire.h>

#include <Adafruit_GFX.h>    // Core graphics library
#include <Adafruit_ST7735.h> // Hardware-specific library for ST7735
#include "Adafruit_miniTFTWing.h"
#include <CyberCitySharedFunctionality.h> // Add shared functionality header

#include <WiFi.h>
#include <PubSubClient.h>
#include "sensitiveInformation.h"

Adafruit_miniTFTWing ss;
#define TFT_RST    -1     // we use the seesaw for resetting to save a pin
#define TFT_CS     14     // Custom CS pin
#define TFT_DC     32     // Custom DC pin
Adafruit_ST7735 tft = Adafruit_ST7735(TFT_CS, TFT_DC, TFT_RST);

WiFiClient espClient;
PubSubClient client(espClient);
CyberCitySharedFunctionality cyberCity;

String lcdStatus = "UNKNOWN";
String lastDisplayedStatus = "";

void updateLCDDisplay(String status) {
  if (status != lastDisplayedStatus) {
    tft.fillScreen(ST77XX_BLACK);
    tft.setCursor(0, 0);
    tft.setTextSize(2);
    tft.setTextColor(ST77XX_WHITE);
    tft.setTextWrap(true);
    tft.print("LCD STATUS:\n");
    tft.setTextSize(3);
    tft.setCursor(0, 40);
    tft.setTextColor(status == "ON" ? ST77XX_GREEN : ST77XX_RED);
    tft.print(status);
    Serial.print("LCD STATUS: ");
    Serial.println(status);
    lastDisplayedStatus = status;
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String message;
  Serial.print("MQTT message received. Topic: ");
  Serial.print(topic);
  Serial.print(" Payload: ");
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
    Serial.print((char)payload[i]);
  }
  Serial.println();
  if (String(topic) == String(mqttTopic)) {
    Serial.print("Processing LCD topic. Value: ");
    Serial.println(message);
    if (message == "1") {
      lcdStatus = "ON";
    } else if (message == "0") {
      lcdStatus = "OFF";
    } else {
      lcdStatus = "UNKNOWN";
    }
    Serial.print("lcdStatus set to: ");
    Serial.println(lcdStatus);
    updateLCDDisplay(lcdStatus);
  }
}

void mqttConnect() {
  while (!client.connected()) {
    if (client.connect(mqttClient)) {
      client.subscribe(mqttTopic);
    } else {
      delay(1000);
    }
  }
}

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

  tft.initR(INITR_MINI160x80);
  tft.setRotation(1);
  tft.fillScreen(ST77XX_BLACK);

  tft.setCursor(0, 0);
  tft.setTextSize(2);
  tft.setTextColor(ST77XX_WHITE);
  tft.setTextWrap(true);
  tft.print("LCD Ready\nConnecting WiFi...");

    Serial.print("Connecting to WiFi SSID: ");
    Serial.println(ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    tft.setCursor(0, 40);
    tft.setTextSize(1);
    tft.setTextColor(ST77XX_YELLOW);
    tft.print("Connecting...");
      Serial.print(".");
  }
    Serial.println();
    Serial.print("WiFi connected! IP: ");
    Serial.println(WiFi.localIP());
  tft.fillScreen(ST77XX_BLACK);
  tft.setCursor(0, 0);
  tft.setTextSize(2);
  tft.setTextColor(ST77XX_GREEN);
  tft.print("WiFi Connected!");

  client.setServer(mqttServer, mqttPort);
  client.setCallback(mqttCallback);
    Serial.print("Connecting to MQTT broker: ");
    Serial.print(mqttServer);
    Serial.print(":");
    Serial.println(mqttPort);
  mqttConnect();

  updateLCDDisplay("UNKNOWN");
}

void loop() {
  if (!client.connected()) {
    mqttConnect();
  }
  client.loop();
  delay(100);
        Serial.print("Subscribed to topic: ");
        Serial.println(mqttTopic);
}

// Helper to draw text on the TFT
void tftDrawText(String text, uint16_t color) {
  tft.fillScreen(ST77XX_BLACK);
  tft.setCursor(0, 0);
  tft.setTextSize(2);
  tft.setTextColor(color);
  tft.setTextWrap(true);
  tft.print(text);

// Example usage of shared functionality (customize as needed)
// cyberCity.someMethod();
}