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
      "FF D8 FF E0 00 10 4A 46 49 46 00 01 01 00 00 01 00 01 00 00 FF DB 00 84 00 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 01 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 FF C2 00 11 08 00 56 02 5B 03 01 22 00 02 11 01 03 11 01 FF C4 00 1D 00 01 00 02 02 03 01 01 00 00 00 00 00 00 00 00 00 00 07 08 05 09 01 04 06 03 02 FF DA 00 08 01 01 00 00 00 00 B9 60 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 6B BF 3B 63 E6 42 A3 5B 90 00 E2 AD 5A 61 09 7A B9 08 00 0E 21 D9 8C 00 00 00 1C 6B 06 F1 C9 75 5E 5C 54 FB 31 02 D8 E8 3B BE B1 91 E4 47 89 CF 60 EF 52 A1 7B 88 AA 7A 98 2A F7 43 BD 8C C2 7B 39 4A AC 4A 58 69 7B C8 F8 CF B6 17 D9 D9 D8 5E 8F DF 1A C9 29 5A 0F 21 11 7E BD E5 5F CB 58 BC 07 81 B4 5D 80 00 1C 6A DA E2 4D 14 56 7D FB D7 6E 96 C6 B0 5A F9 D9 1E BA 6E 04 4B 0A 5B E8 AE 1B D8 AF 1A F3 9F EB B5 A1 9D AA F5 29 D9 2D 4E BF 1A F4 CA DF 0D 79 CC D0 DE 77 23 89 BF 1A F4 D8 B6 06 9B E1 2F 86 BC F6 3B C6 BC 7E D3 86 7A 35 FA FA 28 A2 F2 64 40 00 28 2D FA 6B 87 D5 D8 28 1E 44 89 E7 38 62 F8 EB F3 D0 C9 51 45 A2 8E A2 2B DF C5 17 91 BC 47 B7 B4 75 D3 3B 01 F6 6F 05 11 9E EB B7 AD B4 14 A3 DC 4D 10 2D E0 A2 37 BF AF AD BB 4B 04 FA DB A0 A6 EB 3D 47 FB B6 FA 94 7C EF 14 4F 64 00 00 00 00 00 00 00 00 31 BA FA BE 3E 98 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 3A BF AF CB E5 DC FD 80 00 00 00 00 00 00 00 03 8E 40 FF C4 00 14 01 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 FF DA 00 08 01 02 10 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 0F FF C4 00 15 01 01 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 01 FF DA 00 08 01 03 10 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 15 00 00 00 00 00 FF C4 00 2F 10 00 01 04 03 01 00 00 03 05 08 03 00 00 00 00 00 06 03 04 05 07 01 02 08 00 11 36 37 10 13 17 18 40 14 15 16 20 30 32 60 70 21 34 35 FF DA 00 08 01 01 00 01 08 00 FF 00 73 6F 9C E3 5D B3 86 3D 0F 73 C9 B9 D5 AB 0F C5 EE 8D F5 1A 56 7E 53 1D 34 A9 9F D8 BE D9 D1 15 77 D6 8D B9 4E CE 4E 71 0F 39 FD 55 B6 DB 44 54 DB 5A 62 CB B5 0A 8C F1 1A 51 FC 9D 02 7E 4B 5F 0F C3 BD 80 A6 4A 66 0C EB E8 B9 B9 9F EB C8 BF 6B 15 1E F2 45 E5 6D 74 B7 B3 0B 65 E3 23 3F 5C A7 F6 6F EA 48 9E 10 3E C3 8D 99 9C FC C5 53 DE 60 45 1D 3E 37 89 E8 5A 1E E0 3C 39 37 DE 26 7A FC 34 21 04 0B 6B 2B 01 4B 18 4F 1C 57 6B CB CE F2 EF D5 0C 7A EE BB 31 5B EA 84 4C 42 E7 7D 36 DA 27 F8 A9 6A 4E DE C5 9D 1A ED 07 E7 57 D9 B8 85 A3 2B 19 8A F8 EA FA 27 3A 83 DA 4C 42 E5 3F 97 B9 53 17 79 6E 11 4A 89 D7 73 D3 71 1C F3 61 14 D8 11 B3 EB 90 97 DF E6 C2 76 7C BC 72 85 96 47 47 45 36 44 9D FB 4B FA CD 3C 8E 61 06 1A 35 7B D9 C2 06 69 C1 9D F9 7D B3 A2 0A EF AD 17 71 1E 9C 1D 62 22 76 F7 BA E4 6B C5 D9 C1 40 CA 4D 75 5C 04 62 84 0F E9 1B 4E 46 C8 87 77 89 7F 75 BF CA 43 BE 01 B3 22 6B 3A 0A 19 FB 9A 54 AE E4 B1 9F E6 56 52 F8 B7 CE 81 0E 1B C5 41 4E D9 B7 D9 73 47 44 62 34 2D E1 36 6F 2C B0 D9 2D B9 6B 30 AB E1 51 5F 2C 2C 8E 93 2D 45 49 C8 4A 77 A0 DE 13 4C A2 2E 5C 7D 6F 9F 01 5B 9A 43 BC BD EC 09 2A FC 31 27 B0 FC F3 6A 90 D8 19 9F 64 44 5D 70 9F BB B8 72 1A 2B 7B DB C7 20 66 ED A2 60 A7 6C DB EC B9 A3 A2 31 1A 16 F0 9B 37 96 58 6C 97 DD 05 20 6C C4 37 3A 8B D5 B2 B6 3C 4C A3 F5 00 AC CB 61 1A C8 5A 35 D3 A6 16 47 49 96 A2 A4 E4 25 3B D0 6F 09 A6 51 17 2E F5 A0 F4 9A 2C 1A 66 48 67 9E AD A9 E3 F5 67 23 08 FA 16 D9 9D 00 DA 0A 38 74 78 CE 40 7E A7 68 5E 75 9B AA EE B1 A5 5C A2 14 37 D0 E7 C2 24 38 86 B1 EE 23 19 31 7A D9 D9 18 EF 3E 1E 12 9F 0D CB 3F 20 E8 1B 64 DC 04 AE 32 38 7C 75 E2 F2 03 F0 EF 5C 7E 81 4F EC DF D5 00 7C 51 D9 D3 08 19 5F CA A5 6B E8 91 B6 02 01 B8 81 8E E5 AC FC 2C E5 3D D5 DB E3 15 C4 7E 3D CD 1F 49 17 F7 2E FD 50 C7 AE 4C FE D3 7D BF 4E 45 D2 2D 56 68 BA 0E 85 D8 82 B4 4B 3B 0A D8 8C D2 91 E8 07 AC 56 C6 31 8C 7C 31 5F E7 E1 D1 89 7B A0 F7 C6 95 01 47 C7 91 3F F1 8B 3C 46 D9 27 9D 21 96 CB 74 56 98 DA 9F 25 CE 79 15 B2 38 87 2B 75 8E 97 C6 34 B6 5E 67 1E 75 FF 00 59 7F 72 EF D5 0C 7A FC A5 65 8F DD 33 9F 1E D0 B2 FF 00 AC B4 C2 2F E9 3B BB 36 52 AE A1 E5 3D D6 FF 00 29 0E FB 10 C4 8F 87 77 9B F5 16 5F 18 59 5F 45 E1 9F 54 FD 4B 6B E1 B6 A8 B3 1C 86 6A 8F 3D 69 84 AE 76 29 E9 D5 4B 3A DE C7 68 92 A2 2D 58 B2 16 82 6C C2 EB 49 38 EB CA 47 78 8E B5 19 FB D6 23 E4 E9 58 A6 4E 2C A6 55 84 1B 51 57 6D A9 3B C2 79 8A FC CD 0A E0 9E C5 98 2C 7D D5 3F 52 DA F8 6D AA 2C C7 21 9A A3 CF 5A 61 2B 9D 8A 7A 7A C1 F9 08 BB DC 91 F3 69 17 BA A9 67 5B D8 ED 12 54 45 AB 16 42 D0 4D 98 5D 69 27 1D 79 48 EF 11 ED F4 D1 5D 37 4D 41 BC 6F 4E DF BA B0 54 AF 1B DB DD 01 98 C4 FA BF 75 9B 01 C1 B7 43 97 9A B1 46 B1 D1 74 3A F1 AB 1C 64 41 D6 08 55 74 B7 26 C5 6C EB 92 7E 4E 9F F7 59 6F 8C 9F C3 E9 E1 0F 94 C7 BF 43 BF FC E9 B7 A8 00 A3 28 6B 3E 29 EC A7 B6 D7 5D F5 DB 5D 96 12 B3 69 43 AC C8 44 1E B0 BB 2D 31 CC 4F CD 73 DC 2C C4 2D 60 BB 29 5E 72 0E 2F 83 B1 B0 F2 5B A1 69 79 62 A7 9A 15 8D 39 B1 FA 01 EC 0A 82 2A F3 D5 45 26 0A 83 E9 D9 F2 C0 C3 07 37 FE 65 50 F5 97 5F 9E 00 D9 4E 8A A0 48 DD 5E 37 08 B3 EC C8 F2 E0 F4 F8 FC 49 36 93 32 C1 86 0A 74 32 72 DA 5E F1 B2 33 15 49 1B 18 DE 5D 1F 9E 1F 81 22 4A 67 A1 83 0C 26 EC C7 2F 62 BC E7 19 D9 BA D8 C7 39 07 17 C1 D8 D8 79 2D 7F 57 16 0C B3 F4 E7 C5 15 BA EE B7 50 8A C0 A9 CE 75 0C F0 8B C7 84 E4 5E EA 08 09 D9 F1 78 24 21 AA 10 ED DE 52 8D C6 09 2A D8 6B 12 A4 B2 DC 33 5B A5 03 CB 67 6C 26 EE E2 22 74 DD 38 A6 1A 6F 47 06 18 44 DB CD 5F C9 5F 55 0B AB 1A 3D 9C 94 34 11 F5 FC 05 1B A0 B2 55 1D 28 57 34 57 A1 B1 ED A8 31 93 00 02 08 74 E8 DA C8 AF 16 54 33 C9 BE 9A AF 48 25 4A E2 A6 E0 B9 DC 31 D8 80 06 99 91 E9 40 F2 D9 DB 09 BB B8 88 9D 37 4E 29 86 9B D1 C1 86 11 36 F3 57 F2 5E 22 8C CC D0 FC C4 5E A2 5F 8B 75 29 0B EF DD 97 0D 53 2B 68 0B C1 4D 32 82 3E BF 80 A3 74 16 4A A3 A5 0A E6 8A F4 36 3D FB 3A CC 5B 44 D4 80 2C 43 93 85 72 B3 99 E2 F7 36 40 3B 4B 0C 49 F4 0A F0 9F 8E 14 63 D7 AD 19 B6 01 B7 2F 02 94 64 CA 4E EB D4 27 6B 17 C1 B0 E0 73 57 15 48 FE 46 2E 3E CA 01 B7 E6 A4 59 10 CE 48 E2 71 8D 4C B6 B1 9C FF 00 23 6A 3D 92 9E C1 C7 F8 09 73 E9 68 C1 79 A7 F0 C5 4E EE 7B 9E 4A 39 93 DA E8 39 00 30 E8 A8 04 FF 00 D4 8B 29 94 92 DD 4C 6B 2B F0 CE 9A E1 39 6F BD D9 3D 53 5A 4D 46 CB 38 D3 7D A6 35 C2 8A 69 84 E4 F7 4B 65 B3 B3 59 0D 9D 2B AE 98 FF 00 05 DF 4D 54 D7 3A 6D 8C 63 18 C6 31 FC FF 00 FF C4 00 44 10 00 02 02 01 02 03 04 05 07 08 08 07 00 00 00 00 01 02 03 04 00 05 11 12 13 21 06 14 31 41 10 22 55 61 B3 15 32 40 42 43 51 71 23 30 52 54 81 91 94 B2 16 20 53 60 62 70 82 D3 56 72 95 A2 B1 C3 D2 FF DA 00 08 01 01 00 09 3F 00 FF 00 39 BC 81 C9 E3 B3 3B 02 44 50 D0 49 1F 34 AB DF F4 73 FF 00 C6 56 9A 19 A1 B1 12 D7 12 54 35 77 42 BE 9F 15 46 23 F6 0C B9 04 B5 7B 8C F2 EC 90 24 67 89 3F 3D F3 82 12 32 19 12 87 72 9A 4D CD 1E 47 AE BF D5 9E 28 A6 B1 7C C3 21 78 84 9B A0 42 72 54 92 E4 F2 D9 0E C8 81 06 D1 CA C8 3A 0F A0 39 4A F5 6B C9 3C CF B1 6E 18 E2 52 CC 76 1D 4E C0 66 9C D0 69 D4 E9 73 52 69 8F E5 A5 62 E1 7C 07 45 1F 4F FD 13 96 BB B5 28 A1 B2 AF 2F 03 C9 B1 78 8A 8E 91 82 73 B4 A7 F8 2B 5F ED E5 9E 75 59 AB 49 2D 79 4A 32 6F C1 B8 DF 85 C0 3E 23 35 08 A6 AA 34 E9 A6 08 B0 47 19 E3 42 BE 68 32 CA 41 69 F5 58 60 2E D1 AC 83 81 E3 76 23 67 CB 09 35 BE F5 66 2E 35 8D 63 1C 08 07 92 67 B2 AD 64 11 58 D6 2C C2 64 DE 43 BA 56 8F C9 D8 60 B4 9A 60 8C 4A 49 A5 5B 80 45 F7 94 DB 8C 2E 41 15 7D 5A 8F 01 9D 22 E9 1C A8 FE 12 26 5A 8E 5D 22 95 E5 06 A8 82 20 CF 18 40 4A 71 E4 36 AA E8 B7 E6 2E E1 F4 E1 1D 6E 42 02 E4 23 94 CD 46 17 D3 0E B1 76 B9 88 57 88 1E 08 78 F6 1B E4 CB 15 DA C9 01 89 CA 07 00 BC C8 87 A3 65 B4 9D EA D9 81 21 29 12 45 B0 75 24 FC CC B0 93 E9 14 6F BA 77 41 0C 41 DD 00 E8 9C 79 0C 9A 56 97 34 A0 45 1A D6 80 C6 BC 7D 40 70 E1 DF 34 15 F9 7B 92 CD 7A D2 2A B2 00 0E C1 E3 12 9D 93 1C CF 08 B2 90 DC 86 68 62 8E 68 03 FD 74 68 80 F4 78 AA 31 1F B0 66 A3 14 D5 3B 84 F2 F0 2D 78 E3 3C 69 EF 41 90 42 DA 9D 9A FC F7 9E 51 C6 B0 46 49 45 D8 79 BE 49 20 A9 1C 7C D9 80 8A 8B F0 20 F3 78 D0 65 0E EF 7E A1 4D E5 8D 19 60 B0 8F E0 E9 E8 F6 B1 F8 47 36 9A F4 D3 5E 4A 35 3C E5 90 4E FF 00 F6 0C D5 62 83 40 82 4D 9C 8A 90 83 65 C7 D9 45 97 E2 86 A1 D3 61 9C A1 82 39 0F 1B BB 83 D5 F3 48 B1 43 B3 D1 71 BC 4E 90 C3 24 8F 12 7D 73 CD DC BF FA 30 43 25 BE 43 CF 5A DC 68 23 32 70 78 A3 AA E4 22 CE A5 6C B2 D3 AC 4E C0 F0 F8 C8 FF 00 E0 5C 8A D3 51 46 3B 0A D4 61 30 FE 09 C6 A5 9F 20 8A 1B F2 B9 4A F6 D0 72 83 C8 3E CA 44 F2 7C D4 22 3A 09 B7 56 60 86 BC 60 9A 73 6D C7 B3 E4 E9 1E A1 6A F4 50 40 C5 04 9B 0D 8B B9 D9 B2 D4 73 5A AB C8 96 02 B1 24 5B C4 FB 86 E8 99 A8 45 0D 4F 94 60 A3 D6 BC 72 90 FD 04 CE 4B 8C BF 14 35 0E 99 04 E5 1A 08 E4 25 DD DC 13 BB E6 91 62 87 67 A2 E3 78 9D 21 86 49 1E 24 FA E7 9B B9 7F F4 60 86 4B 7C 87 9E B5 B8 D0 46 64 E0 F1 47 55 F4 41 24 91 4C 96 63 D5 0A 42 25 E1 A8 61 3C 64 E5 59 A7 B6 F5 40 B0 23 81 67 22 2E 21 E4 D9 00 B1 AC DD 84 08 2A 93 C0 38 C2 82 EF 27 DC 8B 91 5A 6A 28 C7 61 5A 8C 26 1F C1 38 D4 B3 E4 11 43 7E 57 29 5E DA 0E 50 79 07 D9 48 9E 4F E8 B0 22 D4 29 43 DE 57 78 D6 50 D1 C5 D6 45 D9 81 FA B9 6A 39 AE D7 58 E7 AE 52 34 8B 78 4F A8 E3 64 CB 51 C3 76 C8 96 79 CB 46 92 ED 08 F5 10 6C F9 73 8E C1 A7 DE E5 E0 8D 63 24 4C 77 86 24 55 D8 71 10 46 53 6A F0 47 F6 35 2B A4 C5 13 C8 CB 2C C3 28 33 C7 BA 2C C4 C0 2B D9 80 1F B4 D9 00 0E 32 E4 42 6E 3A A6 09 F8 56 54 29 33 8C B4 93 CF 06 A3 C9 8C A4 49 16 C9 CB 56 F0 4C BF 14 15 E6 D2 92 77 0F 04 72 EE E6 57 4F 17 C6 0D 34 F4 2B CB 21 03 6D DD E3 0C 4F D0 7F 44 E3 D8 4A B3 43 3B B1 81 82 3E F1 C6 5C 6C 48 39 73 5C FE 22 2F F6 B1 A6 6A B4 E9 4C 91 19 98 33 90 77 6E A4 01 9E C7 B3 FC C9 9E 27 5E 83 E0 CB 9E D0 B9 9E CA B5 80 72 7E 50 D3 A3 20 F8 72 79 71 62 46 D5 DE 26 49 55 F6 E0 31 91 B3 06 DF CB 6C AF A2 A2 15 D8 B6 9E B0 F5 1E F3 16 28 68 EC 76 86 9C 2E 0F 98 90 A2 FA 3F E2 2D 44 7C 5C F3 4A A3 F7 D9 8F 3F 5C AD FC 87 00 31 CB DA FA 88 E0 F9 83 32 67 91 A4 47 F1 51 E2 0E 6B 5C AD 19 3E E4 42 73 CE 95 4F E4 F4 7F 64 DF F8 CF 65 5A C7 88 EA 30 56 15 E6 AD 23 04 13 46 09 60 51 B0 EB 11 D4 88 6C 52 F4 3D EA B6 DF 70 91 C3 65 18 AA EA B5 AB F3 F7 83 7E 4C D1 82 14 90 0E E5 48 F4 7B 58 FC 23 95 AC C9 A4 D0 95 6A F3 CF 58 A2 69 89 70 8B FB 4E E7 21 86 BC DA 7C 6B 4E D5 68 80 50 8F 18 F9 C0 7D CF 9E C4 AF F1 24 C4 55 8A 2D 3E BC 6A A0 74 01 63 03 3C 02 5F 1F BA 26 C2 79 49 A3 41 C9 1F 8B BE 2A 8A A9 A7 57 10 85 F0 2B C0 36 38 8A 27 EF 94 66 08 BF AC B2 23 E2 75 86 47 A3 39 F7 3E F2 47 92 71 D9 1A 74 70 CD EF B7 34 BD D8 FC 2C 76 5D 3E 2A D7 23 EB E7 01 8B BD 43 83 8C D2 8A 59 8B FD F6 AE 9C F6 25 7F 89 26 22 AC 51 69 F5 E3 55 03 A0 0B 18 19 E0 12 F8 FD D1 37 A3 D8 57 FE 03 67 B2 87 C5 18 4F 29 34 68 39 23 F1 77 C5 51 55 34 EA E2 10 BE 05 78 06 C7 11 44 FD F2 8C C1 17 F5 96 44 7F 42 86 46 52 AC A4 6E 08 3E 20 E1 29 4C 6A 06 A1 27 CE A5 CF 98 C7 09 7A 62 FA D2 FC 2A D3 EB 29 CF 52 B1 D5 D0 38 1F E0 85 F8 06 2A F3 A6 D4 6C 1B 27 DE B8 14 5C 71 72 32 7C CC 49 C0 70 6C E1 6B 28 FF 00 91 2D EC 99 ED 8F FD 29 9E 23 41 8B E3 CB 9E CA A9 F0 97 E8 3F 71 CE CD 6B 14 EB 24 16 83 4F 62 9C D0 C6 09 88 81 BB 38 F4 0D C1 04 11 9A 4D 99 D6 09 64 5A B6 52 07 9E BD 88 1F C9 F8 33 43 9E 0A 94 E6 41 4F 4A AB 56 6E 6C CD 2F 8C BC AE AF 9A 6D CA 76 4D EB 6C 20 B3 0B C3 26 C4 0D 8E CF 9D 9C D5 A9 57 F9 36 C2 73 AC D3 96 14 DC ED E6 E3 20 13 DC 58 04 77 6A 03 B3 CA 23 F9 8F 1E 68 5A 81 77 83 BB 3C FF 00 26 CC 2E 18 C8 E1 C8 84 5A 9D D8 84 31 57 DC 13 04 1B EE 77 23 CD F3 B3 7A BC 94 3F A4 94 65 EF 69 4E 63 07 02 3A 6E FC 60 7A 34 EB 53 D6 93 53 6B F5 2D 41 11 98 23 C8 C5 CC 72 85 CD 10 D0 D2 E8 C4 27 EE F0 54 99 25 BD 32 78 22 23 16 77 CD 1E FD 07 96 DD 73 18 B7 5D E0 2E 02 1F 00 F9 D9 BD 5C D0 1D AC A9 37 7B 14 E5 30 72 84 C8 4B F1 E5 2B 16 ED 4B DC F8 20 82 33 2C 8F B5 A8 D8 EC A9 9A 45 FA 0F 2D E8 8C 69 6E 07 80 B8 09 E4 1F 3B 37 AB DC AC 69 55 02 6A D4 E5 9A 3D C0 FB D0 7A 01 24 C6 C0 0F D9 9D 9C D5 A9 57 F9 36 C2 73 AC D3 96 14 DC ED E6 E3 2F EA 56 60 E5 2A D8 D3 62 B2 E0 C6 F1 FD AC 29 9D 87 99 EE C9 54 C0 F6 0D 0B 26 42 08 E1 2E 63 CA E6 AD 89 EA 9A F5 6A 36 C5 D5 1C 87 67 7F 46 93 7A FC B1 EA 65 DD 2A C0 F3 95 1C A2 37 21 33 4C B3 5B BC 8B B1 CF 5E C4 46 19 54 3C CC 43 6C F9 D9 DD 66 D6 91 3C E6 9D BB 10 52 9A 58 1E 3D FD 4B 0A 50 67 67 75 6B D0 0D 22 04 33 55 A9 2C C8 18 3B F4 DD 06 29 56 5A B1 06 52 36 20 85 1B 83 9D 9B D5 EA 54 1D FB 7B 13 D3 96 28 87 1C 6C 06 EE E3 0A 0D 5E 82 32 24 6E 76 16 21 3D 78 33 B3 F7 1C 42 39 55 B9 FA 74 B3 49 10 F2 11 32 74 61 90 CB 10 4B 46 D0 82 CF 4B 16 2C 6F B8 77 4F 24 07 23 E3 9D EA 19 2B 0F 33 34 3F 94 40 3F 12 33 B3 9A AD 2A 74 78 ED 19 6D 54 96 14 2F 18 D9 00 2E 33 44 BF 7C 59 A1 C9 B1 DD 2B 3C FC 0F 01 E8 5F 83 29 CB 5A FD FB 52 58 9E 29 90 A4 A8 07 A8 88 C0 E7 67 75 6B D0 0D 22 04 33 55 A9 2C C8 18 3B F4 DD 06 29 56 5A B1 06 52 36 20 85 1B 83 9D 9B D5 EA 54 1D FB 7B 13 D3 96 28 87 1C 6C 06 EE E3 D0 E1 0D CA 16 2B 06 3E 46 68 CA 6F 9D 99 B8 2E CD 09 AA E9 25 29 27 46 1B 83 BC 65 32 31 17 68 2A D1 43 25 79 7F 27 CD 12 28 77 8B DC EA D9 D9 FB 8E 21 1C AA DC FD 3A 59 A4 88 79 08 99 3A 30 C8 65 88 25 A3 68 41 67 A5 8B 16 37 DC 3B A7 92 03 E9 D8 33 93 42 C7 C5 8B 10 9D B6 A3 59 CF 9B 1D A4 94 E4 9C A7 7D A4 AF 36 DB F2 A6 8F AA 36 68 B3 4D 56 67 DD C7 77 7B 75 25 23 A0 91 5A 2C AB 62 85 34 08 8D 3D 88 0D 64 8E 1F 34 AF 1B 62 24 01 29 C4 94 94 9E 81 AB 10 E8 09 F7 95 CE CA DB 95 AD 38 12 55 9E 9C D2 A1 91 3A 07 8C C5 9A 3D FB F7 F5 28 0B BC 14 AA 4B 32 D3 44 E8 91 1E 58 21 72 2B 49 AA C3 D9 90 20 8E 24 26 75 B0 95 FA 05 4F 1E 30 71 35 B1 0A D6 84 D6 EF F5 DE 11 C7 B9 DF 80 B8 1F DC 28 04 D7 EB 51 9A 6A D1 14 32 09 24 8D 4B 04 E1 5D 89 DF 3B 39 69 05 72 44 50 C5 52 5A F5 E3 67 F1 79 1A 5C 65 77 82 2D E7 90 78 49 34 87 89 DB FC A5 5E 2E 11 BE D9 07 CF 00 AF AD D4 82 48 04 0D BD DB 9C 88 12 DC 3F 5F A0 DD 78 8F 97 96 46 AC 16 40 14 06 DB 60 57 8B AE 44 3D 53 B6 E5 F6 1E 3B 7A DD 3A 64 7C 4B BF AB B1 EA 36 8C 3E D9 08 03 84 B1 6E 3D FC 0E DD 3A 75 FE E3 0D C1 F1 C1 B0 1F 98 FF C4 00 14 11 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 70 FF DA 00 08 01 02 01 01 3F 00 64 FF C4 00 16 11 00 03 00 00 00 00 00 00 00 00 00 00 00 00 00 00 11 50 70 FF DA 00 08 01 03 01 01 3F 00 9D 85 3F FF D9",
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
