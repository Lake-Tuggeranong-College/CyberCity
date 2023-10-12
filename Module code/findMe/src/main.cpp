#define WIRED 0 // 0 = Wireless, 1 = Wired

#include <Arduino.h>
#include "sensitiveInformation.h"
#include "ArduinoJson.h"
#if WIRED == 0
#include "WiFi.h"
#include <HTTPClient.h>
#else
#include <SPI.h>
#include <Ethernet.h>
byte mac[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};
EthernetClient client;
char server[] = "10.177.200.71"; // server IP Address
IPAddress ip(192, 168, 0, 177);
IPAddress myDns(192, 168, 0, 1);
#endif

// EPD - 2.13" EPD with SSD1675
#include "Adafruit_ThinkInk.h"
#define SRAM_CS 32
#define EPD_CS 15
#define EPD_DC 33
#define EPD_RESET -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY -1  // can set to -1 to not use a pin (will wait a fixed delay)
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);
#define COLOR1 EPD_BLACK
#define COLOR2 EPD_RED

unsigned long previousMillis = 0; // will store last time LED was updated
long randNumber;
#define MAX_DELAY 100000 // Time in milliseconds for maximum delay
#define MIN_DELAY 50000  // Time in milliseconds for minimum delay

void logEvent(String eventData)
{
#if WIRED == 0
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
#endif
}

String dataTransfer(String apiKeyValue, String userName, String moduleName, String dataToPost)
{
  String serverResponse;
#if WIRED == 0
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
#else
  serverResponse = "Error: Ethernet";
#endif
  return serverResponse;
}


void updateDisplay(String messageToBroadcast, String ip)
{
  display.setRotation(2);
  display.setTextColor(COLOR1);
  display.setCursor(0, 0);
  display.setTextSize(1);
  display.print(ip);
  display.display();
  Serial.print("Displaying: ");
  Serial.println(messageToBroadcast);
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
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGltZSBTeW5jLg==",
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVXBkYXRlIEJpb2xvZ2ljYWwgSURz",
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGFyZ2V0IEluZmVyaW9ycw==",
      "Jr'er ab fgenatref gb ybir Lbh xabj gur ehyrf naq fb qb V (qb V)",
      "You'll never find me... I'm hidden in plain sight"};

  // Generate random number to indicate index. So each message posted is randomised.
  int messageIndex = random(sizeof(messages) / sizeof(messages[0]));

  // Post the message to the server.
  dataTransfer(apiKeyValue, userName, moduleName, messages[messageIndex]);
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
#if WIRED == 0
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
#else
  Ethernet.init(33); // Most Arduino shields
  if (Ethernet.begin(mac) == 0)
  {
    Serial.println("Failed to configure Ethernet using DHCP");
    // Check for Ethernet hardware present
    if (Ethernet.hardwareStatus() == EthernetNoHardware)
    {
      Serial.println("Ethernet shield was not found.  Sorry, can't run without hardware. :(");
      while (true)
      {
        delay(1); // do nothing, no point running without Ethernet hardware
      }
    }
    if (Ethernet.linkStatus() == LinkOFF)
    {
      Serial.println("Ethernet cable is not connected.");
    }
    // try to configure using IP address instead of DHCP:
    Ethernet.begin(mac, ip, myDns);
  }
  else
  {
    Serial.print("  DHCP assigned IP ");
    Serial.println(Ethernet.localIP());
  }
#endif

#if WIRED == 0
  String ipAddress = WiFi.localIP().toString();
#else
  String ipAddress = Ethernet.localIP().toString();
#endif
  // logEvent("Monitoring Initialised. Avoid squishy biologicals at all costs.");
  String ip = "IP: " + ipAddress;
  // logEvent(ip);
  // Seed needs to be randomised based on ADC#1 noise. ADC#2 can't be used as this is used by Wifi.
  // GPIO pin 36 is AKA pin A4.
  randomSeed(analogRead(36)); // randomize using noise from analog pin 5

  // EPD
  display.begin();
  display.clearBuffer();
  updateDisplay(ip);
}

void loop()
{
  long interval = random(MIN_DELAY, MAX_DELAY);
  unsigned long currentMillis = millis();

  if (currentMillis - previousMillis >= interval)
  {
    // Serial.println(interval);
    previousMillis = currentMillis;
    broadcastMessage();
  }
  delay(250);
}
