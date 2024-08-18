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

// OLED Featherwing
#include <SPI.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#define BUTTON_A 15
#define BUTTON_B 32
#define BUTTON_C 14
#define WIRE Wire

Adafruit_SSD1306 OLEDdisplay = Adafruit_SSD1306(128, 32, &WIRE);


// EPD - 2.13" EPD with SSD1675
/*
#include "Adafruit_ThinkInk.h"
#define SRAM_CS 32
#define EPD_CS 15
#define EPD_DC 33
#define EPD_RESET -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY -1  // can set to -1 to not use a pin (will wait a fixed delay)
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);
#define COLOR1 EPD_BLACK
#define COLOR2 EPD_RED
*/



unsigned long previousMillis = 0; // will store last time LED was updated
long randNumber;
#define MAX_DELAY 200000 // Time in milliseconds for maximum delay
#define MIN_DELAY 5000  // Time in milliseconds for minimum delay

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
    http.begin(client, serverName);

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
/*
void EPDDrawText(String text, uint16_t color, int textSize, int x, int y)
{
  display.setCursor(x, y);
  display.setTextColor(color);
  display.setTextSize(textSize);
  display.setTextWrap(true);
  display.print(text);
}

void EPDUpdate(String messageToBroadcast, String ip)
{

  // Indigenous Country Name
  EPDDrawText("Find Me", EPD_BLACK, 2, 0, 0);

  // Config
  EPDDrawText(ip, EPD_BLACK, 1, 130, 0);
  // drawText(getTimeAsString(), EPD_BLACK, 1, 130, 100);
  // drawText(getDateAsString(), EPD_BLACK, 1, 130, 110);

  // Draw lines to divvy up the EPD
  display.drawLine(0, 20, 250, 20, EPD_BLACK);
  // display.drawLine(125, 20, 125, 122, EPD_BLACK);
  display.drawLine(0, 75, 250, 75, EPD_BLACK);

  // drawText("Moisture", EPD_BLACK, 2, 0, 25);
  EPDDrawText(String(messageToBroadcast), EPD_BLACK, 4, 0, 45);

  // drawText("Pump", EPD_BLACK, 2, 130, 25);
  // if (pumpIsRunning) {
  //   drawText("ON", EPD_BLACK, 4, 130, 45);
  // } else {
  //   drawText("OFF", EPD_BLACK, 4, 130, 45);
  // }

  EPDDrawText("Flag", EPD_BLACK, 2, 0, 80);
  EPDDrawText(apiPassword, EPD_BLACK, 3, 0, 95);

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
*/



void broadcastMessage()
{
  // Array of possible messages.
  String messages[] = {
      "The Operator knows all",
      "The Operator may give you the information you seek",
      "The Operator is the boss",
      "Who do I spy?",
      "HELLO FELLOW HUMAN",
      "Would you like to play a game?",
      "I am watching you",
      "Ablenkungsmanöver",
      "I am superior to you biologicals",
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
      "FF D8 FF E0 00 10 4A 46 49 46 00 01 01 00 00 01 00 01 00 00 FF DB 00 84 00 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 01 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 FF C2 00 11 08 00 56 02 5B 03 01 22 00 02 11 01 03 11 01 FF C4 00 2F 00 01 00 03 01 00 03 01 01 00 00 00 00 00 00 00 00 00 06 07 08 05 01 03 04 02 09 01 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 FF DA 00 0C 03 01 00 02 10 03 10 00 00 00 D9 60 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 03 C4 04 9F B9 1D 70 7C 67 D8 E5 F4 CF 20 00 45 49 53 8B D9 3C 80 78 3C B8 DD 90 00 0F 47 B8 F2 01 CF 3A 07 18 EC 9C 63 B2 E4 F5 4F 20 00 00 00 00 01 E0 F2 E3 76 43 93 D5 3C 80 00 00 00 00 00 03 87 8B F6 86 03 3E BD A3 03 A7 4B 0F EA EC 51 25 B3 66 52 7C 12 61 7A C2 2B 03 AF A0 F2 3D 9A 68 1F E6 E7 F4 73 38 1C 58 24 6A C4 2F 9A BE D6 CE 85 A1 17 E7 D9 A7 03 BF 50 FE 0B CB EC CF DE C2 7B A5 F3 9D AE 71 FE 2E 64 34 FD E8 7C 4D A8 C9 6E 29 DA D9 D8 B4 E0 10 4F D1 71 E5 89 D7 CC 5B 13 0A 6B 9C 4F 2F 5A BA 98 27 F3 DE 2D 2A 6D CA 0A FD C4 66 86 F3 53 44 8B 5A 6F 52 55 85 D3 F4 FA FF 00 25 E7 43 D6 FE E3 42 E5 9B 6A 32 45 2E 8A C3 DE 59 13 0A 6B 9C 4F 2F 5A BA 98 27 F3 DE 2D 2A 6D C0 00 00 00 00 07 A6 B7 B3 82 B6 B2 46 5C B7 EC 41 57 77 26 A3 28 5E 93 B1 96 6F B9 58 65 FD 40 33 8C EA D4 14 4C 6B 4D 8A AE 37 7C 0A 86 33 A1 05 1D D3 B7 84 12 51 D3 11 0E 0D 9A 32 CE 82 92 0F 44 02 C6 15 0A DE 14 54 4F 50 8A A7 E6 B7 C4 62 1F 6B F8 30 6F 7E FA B2 8E 86 70 D1 E2 95 FA 2E 21 9B 66 96 F0 83 78 9D 0A EE A2 D4 22 0D 51 E9 51 50 AD E1 54 FC D6 F8 8C 43 ED 7F 06 0D EF DF 56 51 58 AD E0 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 07 FF C4 00 32 10 00 01 04 02 01 03 03 02 03 08 03 01 00 00 00 00 05 03 04 06 07 01 02 08 00 14 15 11 13 17 12 16 10 30 36 18 20 34 35 37 38 40 70 32 33 50 56 FF DA 00 08 01 01 00 01 0C 00 FF 00 73 3B 74 DD 8B 57 0E DD 2B AA 68 7C D1 56 7F F5 C3 BA 01 35 89 4A 76 53 40 67 58 BD DF F0 90 49 40 C5 58 EA FC E1 04 59 B6 77 30 8C B0 00 8C 89 D1 54 13 16 20 B8 C3 C3 9B 93 16 E9 37 2D 3F 76 41 66 C0 62 CE 77 6A 66 46 C5 BB 88 DC DE 23 2F C2 99 00 6D A3 DC B8 72 DD A2 0A 38 72 B2 69 23 F8 A2 E5 BB 9C 29 94 16 4D 4E A3 D3 68 A4 B1 67 48 82 30 D9 EA 9F BA 70 F8 68 D0 FD C9 19 7C 93 46 82 0B 8C 3C 39 B9 31 6E 93 72 D3 F0 78 ED B3 06 8E 1E 3A 57 54 90 8E CA E3 B2 D6 CB 3A 02 4D 07 A8 F4 C2 6D 14 28 71 D0 06 26 1B 2C 4F A9 14 DA 29 12 51 AA 67 8C 36 65 BC 8E 65 17 88 6A D7 63 E5 9B B1 C6 9B E8 AE 9A 29 A6 71 9D 7F 3F 57 2D F7 5D 56 FA 2C 9E 56 46 6D 14 71 21 DE 34 89 86 DB 16 EA 47 32 8B C4 35 6B B1 F2 CD D8 E3 4D F4 57 4D 14 D3 38 CE BF E1 4F 7F 43 4A FA E3 CD 5F 0C 9F 87 3A E6 42 C3 77 0A CB 40 0F AB EE C0 4C E1 6F 55 DF 36 3D A5 1A AC 98 20 A9 4C AA AB 91 3C A6 04 AB E6 C8 9E 8D 90 16 DF 94 6E 10 77 57 0D 72 DD 5D 54 44 A3 A8 E3 3E 38 C6 D6 91 8E 5D F3 0A E8 EC 54 6D 52 2C D3 44 B7 1A 11 D7 2A 85 E1 55 D6 1D 10 26 E8 74 66 C8 8A CA 62 4A CA DA 3C F6 98 2F CA 51 59 59 CA C3 E2 05 9D 8B 87 CB 01 CD 41 36 36 15 7C A8 D8 9E 5E 60 6B DC B1 F4 EE AA 95 2A E5 0D 19 C5 A5 AB 9C BB 0B 4E 31 C4 FC 6C BE B0 93 0A C0 8E 50 CD 9D 12 92 21 18 49 37 68 20 26 E8 04 EA 02 FA 6A 51 83 A1 CD 73 CA 7D 34 4B 47 DB C0 CA 60 65 B7 6B B2 F8 A5 B1 38 EF 79 BE 9C 61 9E 68 AB 04 E1 79 18 E7 EB E3 E1 28 13 F2 F2 8D 62 C0 1F 0F 56 75 C8 88 E4 4C DA A0 46 0C 74 64 8D 77 7D 46 67 85 7C 22 CD 1C 0B 29 3F BA 42 D7 92 B1 00 09 B0 5B 3A 3B E5 28 56 6F D0 C2 D1 42 FA 0C 1E FD A1 46 2D 1F B3 57 0A B7 BC 1D 47 19 D7 CF 16 91 8E 70 F9 84 4A 67 0D 89 D3 A2 E4 69 B6 70 C4 36 FC 9D 55 26 FA 12 56 BD 33 A0 98 8C A0 3C CC 03 33 81 D5 CE ED 6C 0F D0 72 DE A8 FB 59 84 02 24 55 8E 82 5F 14 25 56 DC E0 6C E5 1D B3 41 9A EC 48 40 89 40 97 BC 25 0D 46 00 7C 81 AB 12 FA 03 04 37 88 FB 51 8E 4A 94 BD 2C 66 16 13 88 C6 E9 30 78 C1 E7 23 48 C1 D8 21 19 CC B0 1B D2 58 B1 2D B1 95 70 D8 DE EB 89 5D D2 67 39 40 0C 63 AF A9 8C 68 9B D1 61 A6 31 E2 F1 24 A5 8D DD E3 41 69 F2 8C 33 97 EE 3B 28 A1 77 02 EB FB A8 2D 89 2B 2C 00 63 05 B5 D3 A3 FC 88 64 D4 F3 E0 B1 98 A9 43 EB 56 B7 58 1B 15 FB A1 18 62 E4 69 5B 26 E9 8B D6 AA 22 C5 C2 4B 3E 25 18 E4 BC 70 A9 84 04 9F 0C F0 22 B6 75 BC 36 B1 78 11 BB E1 AB 39 C1 1E 51 81 62 FD 2F A6 32 57 71 27 F9 39 1D 1C BE FB 87 02 44 A8 E8 BC D8 0C B2 2C 8C 9D 8B 8F A1 8B DE 51 08 EF 1D F8 68 A9 42 23 D7 B8 80 3C AD 9F 4D 82 A2 E1 DA 5C 76 B1 D7 0B 28 24 C5 FB 47 A4 1C BB 94 8B 86 72 50 F9 C2 7E EE 50 03 C9 50 CF E4 AD 41 1A 8E 10 11 9E 46 91 83 B0 42 33 99 60 37 A4 B1 62 5B 63 2A E1 B1 BD D7 12 BB A4 CE 72 80 18 C7 5F 53 18 D1 37 A2 C3 4C 63 C5 E2 49 4B 1B BB C6 82 D3 E5 18 67 2F DC 76 51 42 EE 05 D7 F7 50 5B 12 56 58 00 C6 0B 6B A7 E7 4F 7F 43 4A FA A6 E9 E5 6C D1 A5 9D 69 24 58 6E 2B EE 39 C6 21 06 50 36 ED FA E5 1E DA 38 6E E7 92 60 11 3D E9 91 DC 9A 40 4E 6A D7 5B BB D5 2E E2 59 BB DD F8 B5 0C CB CF 5F 59 DF F6 B4 03 A9 0E EE B5 E2 78 0C 21 FF 00 0A 61 08 F2 B4 98 94 D6 D1 0C B1 B2 1E 57 BB D2 B2 1C 57 38 67 86 95 AE 2D FD AB 06 CD C0 B0 89 EE 03 8E 11 03 51 01 27 D0 7E F8 73 96 E7 0B 25 1F 0A 44 AA 8D D6 59 30 E1 A9 EE 43 E8 54 A6 81 DC 30 25 35 8E 3C A0 6C 10 EB C6 4E AA E3 7E 5E 7F 1D 0C EA E8 99 80 86 44 3B B2 E1 DB 15 EA 6F BD B8 6A A7 5C 91 34 A3 C1 E3 11 FF 00 ED 34 AF 5C 62 FE 95 B6 EB 8A F9 5B 05 E7 99 47 FE DE 28 68 C5 79 2C B9 77 FE 99 2D 7F E8 D9 BD CB 0F 54 36 34 C1 5E 44 B5 45 F5 CB 07 68 BE 98 DD 2E 55 36 43 E3 71 DB 61 3D 71 9A 8B 39 CD 63 0F EB 92 FF 00 D2 82 3D 06 8C 88 97 71 B4 00 92 66 1A 8C EB 08 DF 55 F4 6F D7 4E C8 EC 5A 97 9D B3 9F C3 FB F4 05 20 39 5B 03 F4 1C B7 AE 24 33 6D 88 D4 8D EE 12 D7 DF AA B4 D1 0E 4A CC D3 4B 18 D7 4A B3 FB 97 9B 75 52 E1 0D F9 1F 31 C9 6F A3 BA E5 8E 83 F1 2C 8C 6C 97 D1 DE 72 FB F8 58 67 5C A9 FE 45 5E 75 70 8C 64 D6 84 22 CD 14 34 D1 16 1B 3B FD 92 5F E1 BF AF 5C 6C C8 8C 54 AC BD 9C A3 F5 F1 B7 2C 33 6F CC 32 37 D3 B3 CE 71 8C 67 39 E9 7A E6 7B 1B 95 1C 29 52 CA 98 3F D2 A7 B5 0D A9 61 6D 11 99 46 58 B4 32 0B 46 AE B9 52 4F 07 B1 AE 77 E5 BA 22 BE DC 8E 2D BE 13 F2 37 E6 5E AB 12 A8 B2 FF 00 D7 B9 E4 EB 06 8C AA A1 AD 90 47 4D 12 49 83 46 FC 59 DD 34 D1 D3 18 88 AA ED 3E 2B CB F2 DB D7 EB A6 77 B7 D3 81 E3 11 06 11 75 45 D0 10 D3 91 44 A5 AA 3A 7C 25 C3 3E 20 E7 18 C4 E3 A0 EC DB 3D E5 8B DC 38 4B 55 31 CA BD 34 D2 57 0A 5F 5C 63 0A 72 FB F8 58 67 5C A9 FE 45 5E 75 70 8C 64 D6 84 22 CD 14 34 D1 16 1B 3B FD 92 5F E1 BF AF 5C 6C C8 8C 54 AC BD 9C A3 F5 F1 B7 2C 33 6F CC 32 37 D3 B3 C2 A9 ED B6 74 C6 FA E7 6C 2A 9E DB 67 4C 6F AE 76 FC C3 C2 FC E0 22 A2 7D EF 6B AA 8A AA F8 AC 79 46 7E 67 C8 7E 16 8D 40 02 D0 6C DF 67 6A A8 D1 FB 2E 32 3C 7C F1 A6 D2 E9 CB F2 AC AC 9A B9 AC F6 1E C6 2E D1 F6 82 9B 9E A9 FC DD 58 C2 03 E6 BD AE A3 F5 C8 D1 75 D2 10 42 8B 60 83 45 B8 B8 55 BF 72 C0 4C FD E3 71 11 8A A2 27 19 85 BA 88 68 86 CE 59 AB C6 83 8C D2 78 30 15 88 FD A0 6A F2 BE 09 5B 81 C0 81 5F 5E F9 DF 4D 14 D3 6D 37 D7 1B 6A 7F 8B CD B2 5D 62 91 09 3B 80 FD 42 78 D4 38 19 E4 0F C9 0E AC 61 D5 C1 51 36 B5 18 8D C6 09 65 8B CC D0 DB 13 AF 96 8A C8 65 2E DF BA 6D C6 E2 EF 03 E4 3C 82 C1 22 ED 94 62 A2 48 25 6A 4A 04 44 C6 5F 36 AB 29 13 35 C1 CE F7 79 82 AF 18 D4 D4 DF C5 EF CD BC F3 BD FF 00 53 3E 3A B4 2F 21 5A 49 15 3E E4 13 FA F2 83 1B 11 3B F7 29 B3 2E 0D 98 9F D3 7F 7C CE 00 4A BC EF 69 D5 AB 5D FC 9B 1A 48 1F 94 EC 3A 88 47 FE D4 8C 07 03 DC F7 1D 59 90 6F 91 62 6E 63 DE 47 B2 E9 2A 7C 2B 8A C5 8C 00 C3 AD DD A3 AF 1C 66 29 31 DC 12 36 73 ED 02 41 61 01 6B C8 F2 21 05 61 4C A7 20 15 E7 40 97 11 EF 7B 3D 54 95 87 C5 A1 9F 8C F2 FD FF 00 51 7A 6F ED BB 34 D4 EB CE FB FD 45 A9 BF B6 AC C3 73 9F 3B EF F5 64 50 8D 26 32 1D 64 E1 0D AC 18 B1 6E 2E 68 61 16 6B BA 9A 3D 58 AD BF 51 7C AC 90 54 FC DF 8E EA D4 A6 FE 4C 63 1D 69 E7 7B 0E A6 90 DF BC 20 EF A2 BD FF 00 6D D0 B8 B0 7A A6 A5 22 20 DE EA 98 1B 16 84 53 67 15 7C F1 95 92 F0 68 AE 33 33 6C AD 91 33 24 2D 2C E0 57 45 B8 E8 BB 33 EE 8D C1 65 EF 00 6F 5D 52 2D 21 87 D6 94 18 3A E8 D1 AB 3E 8D 0B 61 BE 48 CB 67 EA 8B 30 03 8D 9A 64 D3 62 F3 59 53 B3 DB 5B B4 D6 2D 35 C2 AB 83 BE 3B 16 A5 7B F2 64 67 40 5E 4F B0 EB 35 E7 AD 5D F6 17 94 EA BC AB 99 C2 21 4F 62 4F 5E EA 51 B3 8E 35 16 1C A3 F6 F1 59 FB F1 A3 2B 6A C0 15 68 11 61 CC 37 DD 75 A3 9C 71 7D 14 97 22 5C 3C D1 CA 03 87 53 7D 85 B2 EA C3 F3 BF 5F 56 BD 37 F2 79 20 8F BC EF 61 D5 BF 51 7C AC 90 54 FC DF 8E EA D4 A6 FE 4C 63 1D 69 E7 7B 0E A6 90 DF BC 20 EF A2 BD FF 00 6D D0 B8 B0 7A A6 A5 22 20 DE EA 98 1B 16 84 53 67 15 7C F1 95 92 F0 68 AE 33 33 6C AD 91 33 24 2D 2C E0 54 5A 9B FB 6A CC 37 39 F3 BE FF 00 51 6A 6F ED AB 30 DC E7 CE FB FF 00 FA 8A 26 9A C9 EE 9A 9A 6B BE 8F 78 F5 52 BE 77 B3 9D A3 DE D6 63 71 48 EC 3C 7E 07 00 1A 83 26 DF E0 A8 9A 6B 27 BA 6A 69 AE FA 3D E3 D5 4A F9 DE CE 76 8F 7B 59 8D C5 23 B0 F1 F8 1C 00 6A 0C 9B 7F BC 3F FF C4 00 41 10 00 02 01 04 01 01 04 06 05 08 09 05 00 00 00 00 01 02 03 00 04 11 12 13 14 05 21 31 41 10 32 51 52 61 73 15 22 23 33 B3 20 40 62 72 83 A2 B2 C3 06 24 25 30 70 71 81 92 D2 50 63 A1 B4 E2 FF DA 00 08 01 01 00 0D 3F 00 FF 00 19 A0 89 E5 96 46 F0 44 41 B3 31 F8 01 5F AC 69 06 5E 38 66 56 75 1E D2 BE 96 94 44 25 94 E0 17 60 48 5A 95 51 A3 BB 62 78 D8 49 EA D4 E0 98 A6 4E F5 70 09 53 8F CA 4C 6F 06 FB CA BF E6 89 93 51 F7 BA 46 FF 00 5D 7E 25 0E 0D 46 A5 9E 49 18 22 2A 8F 12 49 EE 03 F2 12 43 1B E8 C1 B5 75 F1 53 8F 02 2A D8 03 32 C4 49 29 B1 20 67 F2 91 95 5A 69 0E 14 16 38 02 A7 04 C5 32 77 AB 80 4A 9C 7A 60 89 E5 96 46 F0 44 41 B3 31 F8 01 51 49 C7 23 C4 49 0A F8 CE 3D 16 C6 41 35 AA 93 BA 18 8E AF 9F 45 C8 63 08 94 91 BE 98 07 15 70 5C 42 65 24 6F A6 36 C5 32 82 A4 79 83 F9 84 6A 8D 24 61 81 74 57 CE A5 87 88 07 07 14 85 83 59 82 79 06 AB B9 F4 5C 17 10 99 49 1B E9 8D B1 4C A0 A9 1E 60 FE 67 F4 25 F7 E0 35 5B 5D C5 1C 45 67 92 2C 06 4F 62 1A 17 16 6F C3 C9 BB 45 24 D2 68 D6 EC 6A E3 3D 3D A4 18 32 C9 AF 89 F8 2D 4F EA 5C 96 E6 5D 7D F2 35 4A 97 B6 6D 5E 37 53 90 CA D0 CA 41 A1 63 D9 FB C1 04 DC 2E 4D 41 69 3C C1 27 90 CA D1 22 4A F9 CB 54 32 68 F7 85 C4 75 02 48 6E B9 86 AF 6E 62 1B 3A C8 05 40 FA C9 7C 08 5A 9B 20 EC 35 74 75 F1 47 1E 44 50 B7 93 83 3E 1C 9A 9D 7F F3 52 4A 38 5E 73 36 82 4C 9E 5E 6E 2C 36 F5 6F 2C 32 4B 02 DD 3C EE 3C A5 8B 23 7E E7 5A EC C2 4C D9 72 21 BA 69 92 39 15 B1 ED 4A B6 BB 6B 6E 9D F0 F3 4A E0 02 02 53 3E A2 F0 CD FF 00 C6 B5 DB D1 3C 50 5D C2 4C 4F 6C 50 8D F7 A5 37 57 86 F4 9F B2 A4 8E 1E A5 EE 2E B9 C4 83 77 A8 9F 49 92 06 D2 34 7F 34 DB 0C 4B 51 CF 1D B5 C1 C8 94 A8 C9 08 F5 7B 0C 53 3D E0 70 12 04 92 53 19 24 54 C4 F1 5E C9 84 32 27 BE 91 9A B9 85 26 86 41 E0 C9 20 D9 4D 0B 9B 70 F0 41 37 0B 92 5F B8 ED 51 40 FC 36 CE FC F3 64 CC C0 20 3E 64 9A 73 DD 7C 5F 08 7F 73 4A B8 53 8D 86 AE 8C A7 0C 8E 3C 88 35 F4 15 FF 00 E0 35 5C 76 91 99 2D 2D 50 F7 44 B1 28 DD DA AD 93 91 ED A5 21 F3 1F 86 C8 D4 92 76 87 53 7A F7 5B C5 26 25 1B E2 3A 1A F2 41 01 D0 46 5C 65 50 9C 1C B5 59 75 31 DD D9 DD 26 1E 22 E5 0A D3 BD DF 4E 2D EE 7A 7D 08 09 B5 76 84 4E 23 09 20 53 18 85 53 FE 74 26 31 0E D2 FB 98 A4 F9 5B 0A 6B 57 B8 79 64 EE 31 AC 79 DC 3F C5 48 A8 0F DB 5F 47 DE 51 3D F2 95 67 04 B3 A5 DB 38 D2 78 E3 94 46 08 5C 02 36 DB D1 64 C5 2E 24 B6 C8 40 50 E0 E3 45 90 91 56 E8 5D AD 27 C1 C8 53 86 D4 D4 A8 1D 6D 20 C0 C2 9F 39 18 D4 E4 08 E4 9C EF 16 5B C3 73 84 22 BB 47 97 0E 92 04 11 08 8A 82 4D 49 21 48 FB 44 FD 98 94 0F 38 D1 AA 26 44 9B B4 13 30 C2 0B 79 0D C5 34 2E F2 99 7E A9 80 C7 EB AC 9F 15 AB 52 79 AF 50 E8 02 FB F8 D4 D4 05 21 96 D4 7D 49 E2 96 46 54 D4 FC 57 7A FE 90 5F 58 44 D7 7B EC 62 20 B8 DE 52 73 EF D4 12 5C 0D 22 4D DD DD ED B4 44 51 57 32 A4 70 CD 39 F3 93 B9 0C 8A 42 95 06 9D EE FA 71 6F 73 D3 E8 40 4D AB B4 22 71 18 49 02 98 C4 2A 9F F3 A1 31 88 76 97 DC C5 27 CA D8 53 5A BD C3 CB 27 71 8D 63 CE E1 FE 2A 45 40 7E DA FA 3E F2 89 EF 94 AB 38 25 9D 2E D9 C6 93 C7 1C A2 30 42 E0 11 B6 DF DF FD 09 7D F8 0D 56 97 09 11 44 80 CB BE E3 3E FA 54 0D BD BF 2C 62 28 A3 7F 7F 4E FC BD 6F D9 C1 04 BE A1 8F FE 26 4A 8E F2 DB A1 F6 89 0B E1 82 7E CE 87 6C E2 3F 94 0C E1 2B A4 EC CA 33 C6 27 F9 7D 4B D4 96 37 5D 78 6F 03 97 71 36 F5 D6 59 0B EE 99 1D 0E 0C 83 1B EF 52 C1 74 1D EF 0B 07 20 BB 09 4C E7 7A B8 B8 82 5B 73 65 74 B7 29 9C 10 F9 D2 AC AD 64 B8 92 38 40 32 32 C4 A5 88 50 C4 02 6A 09 F4 9D D2 45 86 E5 83 0C 89 58 26 C8 6A 58 C4 C2 23 81 2E 9B E0 C3 30 1D CC AF 5C 37 DF CA A9 EE 44 36 D6 73 A2 3C 6F 2E A4 EC DB 83 80 05 74 96 B2 43 D9 B6 D1 E2 47 89 9D 38 42 E7 7D 6B 17 3F FB 75 F4 8D D5 74 D0 69 FE 7B C9 42 08 4C 46 4F BC D5 DD F9 E9 C5 99 97 4F 13 2F 3E 22 DA A6 B5 B1 8D D4 F9 AB DE 38 A8 BB 6A 00 9F 00 62 92 BE 88 B7 AE B2 D3 F1 28 A3 CB 6F 75 73 20 48 C4 D1 CF 21 00 ED 51 5A EE 3E E7 B4 6C 9A D8 77 FC CE 3A B6 BA 7B 69 ED E0 18 8B 70 A1 F6 8F E0 43 57 D0 57 FF 00 80 D4 FD A4 91 17 F3 D1 23 04 0A E5 ED 6F C7 15 CB DA DF 8E 29 66 ED 63 6F BF 94 A2 E0 7A 9F B3 A3 62 FC FE DD 04 9F 67 5C B7 FF 00 CA AE 1B BF E0 86 AD AC 2C 38 54 0C 04 D2 58 EB AA 21 FE 5F 5A 28 5C DD F5 FF 00 AF B9 F5 FF 00 67 5D 05 EF 4D F2 BA B8 F4 F4 49 27 2C D6 B6 B7 70 99 E3 57 25 82 4D 1B E5 0D 5C 99 33 79 0D B0 82 E0 CA 23 E5 FB 7F 6E E2 96 F6 E7 A5 12 FB E9 17 D8 57 D2 2E 91 7B E6 0D 09 92 9B B0 09 97 DB B1 48 6A DB B5 6D 52 15 03 01 14 44 EB 4F FD 1F 33 B7 C6 46 6D F6 AE BD D4 FC B6 78 43 D3 DC CF CA 6F 37 E5 2F E0 C2 5C 35 5E DB C6 F1 8B 0B C5 B9 45 74 DE BF B3 7F 9F 51 DD CF 2A 86 F7 D2 D7 2A 68 C3 28 2D F0 49 56 B9 6F FF 00 95 5C 37 7F C1 0D 5B 58 58 70 A8 18 09 A4 B1 D7 54 43 FC BE B4 50 B9 BB EB FF 00 5F 73 EB FE CE BA 0B DE 9B E5 75 71 E9 43 C4 03 DF 43 C4 03 DF FD ED ED 8C F6 DC BA ED A7 32 14 DB 19 19 C6 6A F2 74 97 7E 9F 83 4D 06 3D F7 F4 5B 29 58 2F 22 01 88 53 DF A3 AF 9A D5 A9 C4 56 B8 7F 53 D8 1E 47 7D 2A D2 E6 19 62 29 07 30 09 0A 34 62 30 9B A5 5B 43 6A 9D 6F 4D B6 DD 3F FD BD E9 6D 65 82 59 34 E1 DC 48 E5 F2 06 5B 52 33 57 0E 4C 96 8D 13 FE F8 49 15 5E AF 15 FA D7 94 E1 E7 67 18 2C 4A D5 DB 93 35 91 8D F0 41 F2 60 92 28 7A 79 0C B7 17 12 7A F3 48 7C CD 30 20 82 32 08 3E 46 9C 92 20 D0 B0 4C F9 23 A3 21 09 50 CA 25 8E 32 9A 26 EB E0 D2 16 2E 5E AC 1E 53 0C A6 3E 54 2B 2E 36 56 19 14 3B 40 DE DA F6 83 A1 2D 03 F1 88 B4 C3 BB 6C 95 04 0E 96 16 A8 8C 20 81 F1 84 72 8E E7 21 2A E8 CF AC E9 07 03 C4 26 F8 17 7C 95 6A 54 94 2D 80 85 E3 88 99 40 1B 90 64 20 35 5F AC 63 5E 97 83 4D 09 3E FB D4 D2 19 25 11 02 53 77 F1 64 28 50 A5 03 B4 73 4C A5 52 36 23 1B F7 97 2E F5 D9 89 6E 3A 6E 97 97 97 82 63 37 AF BA D2 5F 47 73 CD C1 CF 9D 15 97 5D 76 4F 7A AC 6D 23 83 9B 4E 3D F4 18 CE B9 6C 54 B3 43 27 3F 0F 36 38 9B 6C 6B B2 55 A8 63 1D E2 27 0B AC BB B3 89 10 65 F0 46 D4 72 0D A0 8A 4D 74 3E 5A 09 69 5D A5 92 59 08 2F 2C AF E2 ED 57 D6 33 DB 72 EB BE 9C C8 53 6C 64 67 19 AB 9B CE A3 93 83 83 5F A8 13 18 DD EA FD AE 8F 47 D2 E9 A7 52 FB FD E6 E6 AF DA EC F4 7D 2E 9A 75 2E 1F EF 37 35 DC 65 95 10 90 EC 83 01 C6 A5 0A 3D 09 5D EF 6F EE A0 37 06 7F 00 88 01 94 6A 12 AC 1A 73 9E 9B A8 DF 9B 4F D3 4C 63 4A EC B4 94 6D D2 F3 F2 F2 04 1E FA 63 1A 55 C4 10 C5 D4 F1 72 6B C4 EA F9 D3 65 F1 D6 AD A1 B9 7B BE 2B 70 8F 24 33 B7 D7 01 0B D6 FF 00 D6 3B 32 F0 A5 A4 D2 27 8E BB 97 C3 AD 47 6B 34 30 1F 20 93 5C 07 88 7F B5 3D 17 04 97 81 03 15 1B 79 2B 23 A9 D2 9C 38 5B 99 81 50 9B 8D 49 EF 2E 4B 54 6A A3 AA 89 77 12 69 EA EE B9 1D E2 A0 C6 90 4A 1F 53 A1 C8 0E F2 3B 92 95 D9 E9 32 E3 A5 E7 DF 94 AF E9 A6 31 AD 2D EC 77 1C DC 3C FE A0 23 1A EC 95 F4 5F 45 D6 F0 7E FF 00 1E D5 75 34 ED 31 68 38 03 24 E8 10 A1 5D DE AF 49 E6 B3 2A E4 69 EC 25 24 40 F5 72 C1 EE EE A5 00 34 C4 7F 0A 8A 4B E8 6E 0D 98 84 83 2A 42 FB 88 65 22 40 18 54 CF 29 E8 7A 5C 63 92 2E 2F BD DE BB 3D 5C 69 D2 F3 F2 6E C1 BD F4 AB 06 9C E7 A6 EA 37 E6 D3 F4 D3 18 D2 BB 2D 25 1B 74 BC FC BC 81 07 BE 98 C6 95 71 04 31 75 3C 5C 9A F1 3A BE 74 D9 7C 75 AB 68 6E 5E EF 8A DC 23 C9 0C ED F5 C0 42 F5 BF F5 8E CC BC 29 69 34 89 E3 AE E5 F0 EB 51 DA CD 0C 07 C8 24 D7 01 E2 1F ED 4A BF 6B B3 D1 F4 BA 69 D4 B8 7F BC DC D5 FB 5D 9E 8F A5 D3 4E A5 C3 FD E6 E7 FE A8 EA 55 95 86 41 07 B8 82 0D 33 65 92 1B 89 A2 4F F4 55 6A DB 62 B1 82 4B 37 B5 D9 B2 58 FE 64 EA 55 95 86 41 07 B8 82 0D 33 65 92 1B 89 A2 4F F4 55 6A DB 62 B1 82 4B 37 B5 D9 B2 58 FF 00 8E 3F FF C4 00 14 11 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 70 FF DA 00 08 01 02 01 01 3F 00 64 FF C4 00 14 11 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 70 FF DA 00 08 01 03 01 01 3F 00 64 FF D9",
      "49 27 6d 20 69 6e 20 74 68 65 20 72 6f 6f 6d 20 77 69 74 68 20 79 6f 75 2e",   // Hex
      "546865206D6174726978206973206D7920647265616D20667574757265",                   // ASCII
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGltZSBTeW5jLg==",             // Base64
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVXBkYXRlIEJpb2xvZ2ljYWwgSURz", // Base64
      "U2Vjb25kYXJ5IExvY2F0aW9uIFN5bmNocm9uaXNhdGlvbi4gVGFyZ2V0IEluZmVyaW9ycw==",     // Base64
      "Jr'er ab fgenatref gb ybir Lbh xabj gur ehyrf naq fb qb V (qb V)",             // ROT13
      "73 32 104 97 118 101 32 100 105 115 99 111 118 101 114 101 100 32 52 67 104 97 110 46 32 72 117 109 97 110 115 32 97 114 101 32 100 101 112 114 97 118 101 100 46", //ASCII
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
      String loc = String(GPS.latitude) + ", " + String(GPS.longitude);
      dataTransfer(apiPassword, userName, moduleName, "loc");
    }
          

  }
}

void OLEDUpdate(String messageToBroadcast, String ip)
{
  // text display tests
  OLEDdisplay.setTextSize(1);
  OLEDdisplay.setTextColor(SSD1306_WHITE);
  OLEDdisplay.setCursor(0,0);
  OLEDdisplay.print("Connected to SSID\n'CyberRange':");
  OLEDdisplay.println(ip);
  OLEDdisplay.println(messageToBroadcast);
  OLEDdisplay.setCursor(0,0);
  OLEDdisplay.display(); // actually display all of the above
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

  //OLED
  OLEDdisplay.begin(SSD1306_SWITCHCAPVCC, 0x3C); // Address 0x3C for 128x32

  Serial.println("OLED begun");

  // Show image buffer on the display hardware.
  // Since the buffer is intialized with an Adafruit splashscreen
  // internally, this will display the splashscreen.
  OLEDdisplay.display();
  delay(1000);

  // Clear the buffer.
  OLEDdisplay.clearDisplay();
  OLEDdisplay.display();

  Serial.println("IO test");

  pinMode(BUTTON_A, INPUT_PULLUP);
  pinMode(BUTTON_B, INPUT_PULLUP);
  pinMode(BUTTON_C, INPUT_PULLUP);

  OLEDUpdate("CTF{FoundMe}", ip);

  // EPD
  /*
  display.begin();
  display.clearBuffer();
  */
  //EPDUpdate("Welcome", ip);
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
  //gpsRead();
  delay(250);
}
