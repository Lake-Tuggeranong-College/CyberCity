/*
 This code is for the ESP32 to broadcast a message to the server every 30 seconds.
  The message is randomised from a list of possible messages.
  The ESP32 will also log the IP address to the server.
  The ESP32 will also log the event to the server.

*/

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

// GPS
#include <Adafruit_GPS.h>
// what's the name of the hardware serial port?
#define GPSSerial Serial1
// Connect to the GPS on the hardware port
Adafruit_GPS GPS(&GPSSerial);
// Set GPSECHO to 'false' to turn off echoing the GPS data to the Serial console
// Set to 'true' if you want to debug and listen to the raw GPS sentences
#define GPSECHO false
uint32_t timer = millis();

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
    // Serial.println(eventLogURL);
    //  Your Domain name with URL path or IP address with path
    http.begin(client, eventLogURL);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Send HTTP POST request, and store response code
    http.addHeader("Content-Type", "application/json");
    String postJSONString = "{\"userName\":\"" + userName + "\",\"eventData\":\"" + eventData + "\"}";

    Serial.print("Debug JSON String: ");
    Serial.println(postJSONString);
    int httpResponseCode = http.POST(postJSONString);
    // String serverResponse = http.getString();
    // Serial.println(serverResponse);
    if (httpResponseCode > 0)
    {
      if (httpResponseCode == 500)
      {
        Serial.println("Data Accepted by Server");
      }
      else
      {
        Serial.print("HTTP Response code: ");
        Serial.print(httpResponseCode);
        Serial.println(".");
      }
    }
    else
    {
      if (httpResponseCode == -1)
      {
        Serial.println("Server refused connection - check server is running");
      }
      else
      {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
      }
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

String dataTransfer(String apiKeyValue, String userName, String Location, String dataToPost)
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
    String postJSONString = "{\"api_key\":\"" + apiKeyValue + "\",\"sensor\":\"" + userName + "\",\"location\":\"" + Location + "\",\"sensorValue\":\"" + dataToPost + "\"}";
    // String postJSONString = "{\"api_key\":\"" + apiKeyValue + "\",\"sensor\":\"" + sensorName + "\",\"location\":\"" + sensorLocation + "\",\"sensorValue\":\"" + dataToPost + "\"}";

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

void drawText(String text, uint16_t color, int textSize, int x, int y)
{
  display.setCursor(x, y);
  display.setTextColor(color);
  display.setTextSize(textSize);
  display.setTextWrap(true);
  display.print(text);
}

void updateEPD(String messageToBroadcast, String ip)
{

  // Indigenous Country Name
  drawText("Find Me", EPD_BLACK, 2, 0, 0);

  // Config
  drawText(ip, EPD_BLACK, 1, 130, 0);
  // drawText(getTimeAsString(), EPD_BLACK, 1, 130, 100);
  // drawText(getDateAsString(), EPD_BLACK, 1, 130, 110);

  // Draw lines to divvy up the EPD
  display.drawLine(0, 20, 250, 20, EPD_BLACK);
  // display.drawLine(125, 20, 125, 122, EPD_BLACK);
  display.drawLine(0, 75, 250, 75, EPD_BLACK);

  // drawText("Moisture", EPD_BLACK, 2, 0, 25);
  drawText(String(messageToBroadcast), EPD_BLACK, 4, 0, 45);

  // drawText("Pump", EPD_BLACK, 2, 130, 25);
  // if (pumpIsRunning) {
  //   drawText("ON", EPD_BLACK, 4, 130, 45);
  // } else {
  //   drawText("OFF", EPD_BLACK, 4, 130, 45);
  // }

  drawText("Flag", EPD_BLACK, 2, 0, 80);
  drawText(apiPassword, EPD_BLACK, 3, 0, 95);

  // logEvent("Updating the EPD");
  display.display();
}

void updateDisplay(String messageToBroadcast, String ip)
{
  display.setRotation(1);
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
      "546865206D6174726978206973206D7920647265616D20667574757265",                   // ASCII
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGltZSBTeW5jLg==",             // Base64
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVXBkYXRlIEJpb2xvZ2ljYWwgSURz", // Base64
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGFyZ2V0IEluZmVyaW9ycw==",     // Base64
      "Jr'er ab fgenatref gb ybir Lbh xabj gur ehyrf naq fb qb V (qb V)",             // ROT13
      "73 32 104 97 118 101 32 100 105 115 99 111 118 101 114 101 100 32 52 67 104 97 110 46 32 72 117 109 97 110 115 32 97 114 101 32 100 101 112 114 97 118 101 100 46",
      "You'll never find me... I'm hidden in plain sight"};

  // Generate random number to indicate index. So each message posted is randomised.
  int messageIndex = random(sizeof(messages) / sizeof(messages[0]));

  // Post the message to the server.
  dataTransfer(apiPassword, userName, moduleName, messages[messageIndex]);
}

void gpsInitialise()
{
  GPS.begin(9600);
  // uncomment this line to turn on RMC (recommended minimum) and GGA (fix data) including altitude
  GPS.sendCommand(PMTK_SET_NMEA_OUTPUT_RMCGGA);
  // uncomment this line to turn on only the "minimum recommended" data
  // GPS.sendCommand(PMTK_SET_NMEA_OUTPUT_RMCONLY);
  // For parsing data, we don't suggest using anything but either RMC only or RMC+GGA since
  // the parser doesn't care about other sentences at this time
  // Set the update rate
  GPS.sendCommand(PMTK_SET_NMEA_UPDATE_1HZ); // 1 Hz update rate
  // For the parsing code to work nicely and have time to sort thru the data, and
  // print it out we don't suggest using anything higher than 1 Hz

  // Request updates on antenna status, comment out to keep quiet
  GPS.sendCommand(PGCMD_ANTENNA);

  delay(1000);

  // Ask for firmware version
  GPSSerial.println(PMTK_Q_RELEASE);
}

void gpsRead()
{
  // read data from the GPS in the 'main loop'
  char c = GPS.read();
  // if you want to debug, this is a good time to do it!
  if (GPSECHO)
    if (c)
      Serial.print(c);
  // if a sentence is received, we can check the checksum, parse it...
  if (GPS.newNMEAreceived())
  {
    // a tricky thing here is if we print the NMEA sentence, or data
    // we end up not listening and catching other sentences!
    // so be very wary if using OUTPUT_ALLDATA and trying to print out data
    Serial.print(GPS.lastNMEA());   // this also sets the newNMEAreceived() flag to false
    if (!GPS.parse(GPS.lastNMEA())) // this also sets the newNMEAreceived() flag to false
      return;                       // we can fail to parse a sentence in which case we should just wait for another
  }

  // approximately every 2 seconds or so, print out the current stats
  if (millis() - timer > 2000)
  {
    timer = millis(); // reset the timer
    Serial.print("\nTime: ");
    if (GPS.hour < 10)
    {
      Serial.print('0');
    }
    Serial.print(GPS.hour, DEC);
    Serial.print(':');
    if (GPS.minute < 10)
    {
      Serial.print('0');
    }
    Serial.print(GPS.minute, DEC);
    Serial.print(':');
    if (GPS.seconds < 10)
    {
      Serial.print('0');
    }
    Serial.print(GPS.seconds, DEC);
    Serial.print('.');
    if (GPS.milliseconds < 10)
    {
      Serial.print("00");
    }
    else if (GPS.milliseconds > 9 && GPS.milliseconds < 100)
    {
      Serial.print("0");
    }
    Serial.println(GPS.milliseconds);
    Serial.print("Date: ");
    Serial.print(GPS.day, DEC);
    Serial.print('/');
    Serial.print(GPS.month, DEC);
    Serial.print("/20");
    Serial.println(GPS.year, DEC);
    Serial.print("Fix: ");
    Serial.print((int)GPS.fix);
    Serial.print(" quality: ");
    Serial.println((int)GPS.fixquality);
    if (GPS.fix)
    {
      Serial.print("Location: ");
      Serial.print(GPS.latitude, 4);
      Serial.print(GPS.lat);
      Serial.print(", ");
      Serial.print(GPS.longitude, 4);
      Serial.println(GPS.lon);
    }
  }
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
  logEvent("Monitoring Initialised. Avoid squishy biologicals at all costs.");
  String ip = "IP: " + ipAddress;
  logEvent(ip);
  // Seed needs to be randomised based on ADC#1 noise. ADC#2 can't be used as this is used by Wifi.
  // GPIO pin 36 is AKA pin A4.
  randomSeed(analogRead(36)); // randomize using noise from analog pin 5

  // GPS
  gpsInitialise();

  // EPD
  display.begin();
  display.clearBuffer();
  updateEPD("Welcome", ip);
}

void loop()
{

  // Start : Broadcast a message
  // 'randomly' broadcast a messages.
  // Unlike using a delay,() this does not stop the rest of the loop from executing.
  long interval = random(MIN_DELAY, MAX_DELAY);
  unsigned long currentMillis = millis();

  if (currentMillis - previousMillis >= interval)
  {
    // Serial.println(interval);
    previousMillis = currentMillis;
    broadcastMessage();
  }
  // End : Broadcast a message

  // GPS
  gpsRead();
  delay(250);
}
