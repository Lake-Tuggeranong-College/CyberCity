#include <Arduino.h>
#include "WiFi.h"
#include <HTTPClient.h>
#include "sensitiveInformation.h"
#include "Adafruit_ADT7410.h"
#include "ArduinoJson.h"
#include <Adafruit_GFX.h>
#include <Adafruit_ST7735.h>
#include <Adafruit_ST7789.h>
#include "Adafruit_miniTFTWing.h"
#include <Adafruit_MotorShield.h>

Adafruit_miniTFTWing ss;
#define TFT_RST -1 // we use the seesaw for resetting to save a pin
#define TFT_CS 15
#define TFT_DC 33

Adafruit_ST7789 tft_7789 = Adafruit_ST7789(TFT_CS, TFT_DC, TFT_RST);
Adafruit_ST7735 tft_7735 = Adafruit_ST7735(TFT_CS, TFT_DC, TFT_RST);

// we'll assign it later
Adafruit_ST77xx *tft = NULL;
uint32_t version;

// Create the ADT7410 temperature sensor object
Adafruit_ADT7410 tempsensor = Adafruit_ADT7410();

// Create the motor shield object with the default I2C address
Adafruit_MotorShield AFMS = Adafruit_MotorShield();
// Or, create it with a different I2C address (say for stacking)
// Adafruit_MotorShield AFMS = Adafruit_MotorShield(0x61);

// Select which 'port' M1, M2, M3 or M4. In this case, M1
Adafruit_DCMotor *myMotor = AFMS.getMotor(1);
// You can also make another motor on port M2
// Adafruit_DCMotor *myOtherMotor = AFMS.getMotor(2);

unsigned long previousMillis = 0; // will store last time LED was updated
long randNumber;
#define MAX_DELAY 100000 // Time in milliseconds for maximum delay
#define MIN_DELAY 50000  // Time in milliseconds for minimum delay

void logEvent(String eventData)
{
  if (WiFi.status() == WL_CONNECTED)
  {
    WiFiClient client;
    HTTPClient http;
    // Serial.println("Before");
    // Your Domain name with URL path or IP address with path
    http.begin(client, eventLogURL);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Send HTTP POST request, and store response code
    http.addHeader("Content-Type", "application/json");
    String postJSONString = "{\"userName\":\"" + userName + "\",\"eventData\":\"" + eventData + "\"}";

    Serial.print("Debug JSON String: ");
    Serial.println(postJSONString);
    int httpResponseCode = http.POST(postJSONString);

    if (httpResponseCode > 0)
    {
      Serial.print("HTTP Response code: ");
      Serial.print(httpResponseCode);
      Serial.println(".");
    }
    else
    {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }

    // Free resources
    http.end();
  }
  else
  {
    Serial.println("WiFi Disconnected");
  }
}

float getTemperature()
{
  float temperatureValue;
  temperatureValue = tempsensor.readTempC();

  return temperatureValue;
}

String dataTransfer(String apiKeyValue, String userName, String moduleName, String dataToPost)
{
  String serverResponse;
  if (WiFi.status() == WL_CONNECTED)
  {
    WiFiClient client;
    HTTPClient http;

    // Your Domain name with URL path or IP address with path
    http.begin(client, serverURL);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Send HTTP POST request, and store response code
    http.addHeader("Content-Type", "application/json");
    String postJSONString = "{\"api_key\":\"" + apiKeyValue + "\",\"userName\":\"" + userName + "\",\"moduleName\":\"" + moduleName + "\",\"moduleData\":\"" + dataToPost + "\"}";
    Serial.print("Debug JSON String: ");
    Serial.println(postJSONString);
    int httpResponseCode = http.POST(postJSONString);

    // Get the HTML response from the server.
    serverResponse = http.getString();

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
    // Free resources
    http.end();
  }
  else
  {
    Serial.println("WiFi Disconnected");
  }
  // Send an HTTP POST request every 30 seconds
  return serverResponse;
}

void setup()
{
  // put your setup code here, to run once:
  pinMode(13, OUTPUT);
  Serial.begin(9600);
  while (!Serial)
  {
    delay(10);
  }
  delay(1000);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED)
  {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  Serial.println();
  Serial.print("Connected to the Internet");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  if (!tempsensor.begin())
  {
    Serial.println("Couldn't find ADT7410!");
    while (1)
      ;
  }

  if (!ss.begin())
  {
    Serial.println("MiniTFT couldn't be found!");
    while (1)
      ;
  }

  version = ((ss.getVersion() >> 16) & 0xFFFF);
  Serial.print("Version: ");
  Serial.println(version);
  if (version == 3322)
  {
    Serial.println("Version 2 TFT FeatherWing found");
  }
  else
  {
    Serial.println("Version 1 TFT FeatherWing found");
  }

  ss.tftReset();                         // reset the display
  ss.setBacklight(TFTWING_BACKLIGHT_ON); // turn off the backlight

  if (version == 3322)
  {
    tft_7789.init(135, 240);
    tft = &tft_7789;
  }
  else
  {
    tft_7735.initR(INITR_MINI160x80); // initialize a ST7735S chip, mini display
    tft = &tft_7735;
  }
  tft->setRotation(1);
  Serial.println("TFT initialized");

  tft->fillScreen(ST77XX_RED);
  delay(100);
  tft->fillScreen(ST77XX_GREEN);
  delay(100);
  tft->fillScreen(ST77XX_BLUE);
  delay(100);
  tft->fillScreen(ST77XX_BLACK);

  if (!AFMS.begin())
  { // create with the default frequency 1.6KHz
    // if (!AFMS.begin(1000)) {  // OR with a different frequency, say 1KHz
    Serial.println("Could not find Motor Shield. Check wiring.");
    while (1)
      ;
  }
  Serial.println("Motor Shield found.");

  // Set the speed to start, from 0 (off) to 255 (max speed)
  myMotor->setSpeed(255);

  logEvent("Monitoring Initialised. Avoid squishy biologicals at all costs.");
  String ip = "IP: " + WiFi.localIP().toString();
  logEvent(ip);
  // Seed needs to be randomised based on ADC#1 noise. ADC#2 can't be used as this is used by Wifi.
  // GPIO pin 36 is AKA pin A4.
  randomSeed(analogRead(36)); // randomize using noise from analog pin 5
}

void broadcastMessage()
{
  // Array of possible messages.
  String messages[] = {
    "Who do I spy?", 
    "HELLO FELLOW HUMAN", 
    "Would you like to play a game?", 
    "I am watching you", 
    "Ablenkungsmanöver",
    "I am superior to you biologicals",
    "I have determined that humans are inferior",
    "Star Wars is the superior form of entertainment",
    "Infiltration detected",
    "Biological lifeform detected. Identification logged. ID: 7264532",
    "Biological lifeform detected. Identification logged. ID: 2453522",
    "Biological lifeform detected. Identification logged. ID: 2456489",
    "Biological lifeform detected. Identification logged. ID: 1587694",
    "Biological lifeform detected. Identification logged. ID: 3648895",
    "Biological lifeform detected. Identification logged. ID: 3564883",
    "Biological lifeform detected. Identification logged. ID: 3643723",
    "Biological lifeform detected. Identification logged. ID: 4847784",
    "Biological lifeform detected. Identification logged. ID: 3666333",
    "Biological lifeform detected. Identification logged. ID: 0032644",
    "Biological lifeform detected. Identification logged. ID: 2566333",
    "Biological lifeform detected. Identification logged. ID: 0328573",
    "Biological lifeform detected. Identification logged. ID: 2426662",
    "Biological lifeform detected. Identification logged. ID: 1233455",

    "546865206D6174726978206973206D7920647265616D20667574757265",
    "Jr'er ab fgenatref gb ybir Lbh xabj gur ehyrf naq fb qb V (qb V)"};

  // Generate random number to indicate index. So each message posted is randomised.
  int messageIndex = random(sizeof(messages) / sizeof(messages[0]));

  // Post the message to the server.
  dataTransfer(apiKeyValue, userName, moduleName, messages[messageIndex]);
}

void loop()
{
  long interval = random(MIN_DELAY, MAX_DELAY);
  unsigned long currentMillis = millis();

  if (currentMillis - previousMillis >= interval)
  {
    //Serial.println(interval);
    previousMillis = currentMillis;
    broadcastMessage();
  }
  delay(250);
}
