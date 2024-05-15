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
      "FF D8 FF E0 00 10 4A 46 49 46 00 01 01 00 00 01 00 01 00 00 FF DB 00 84 00 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 01 05 05 05 05 05 05 06 06 06 06 08 09 08 09 08 0C 0B 0A 0A 0B 0C 12 0D 0E 0D 0E 0D 12 1B 11 14 11 11 14 11 1B 18 1D 18 16 18 1D 18 2B 22 1E 1E 22 2B 32 2A 28 2A 32 3C 36 36 3C 4C 48 4C 64 64 86 FF C2 00 11 08 00 56 02 5B 03 01 22 00 02 11 01 03 11 01 FF C4 00 1D 00 01 01 00 03 01 01 01 01 01 00 00 00 00 00 00 00 00 08 05 06 07 09 03 04 01 02 FF DA 00 08 01 01 00 00 00 00 B2 C0 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 01 AE CC F5 6C 87 64 80 44 FA 3E D3 73 80 93 6B 20 1C 0B AF 67 CC 4C 3F 88 EC F8 6A FC 1A 74 61 F7 B6 F3 C0 0F E7 1D EC 60 02 0B D6 2C 29 CD 69 CB 1D 73 E3 2F D0 34 13 CD BF 49 22 8F AD 43 23 6D 74 74 89 D1 39 2E DD BD E5 B8 E5 57 34 64 2C 29 C7 7C 9C B3 B6 13 19 08 7A 05 E6 17 57 A6 79 4E 81 62 67 BC EC BC F5 A9 5F 3F 8B EE 58 CE 39 55 C9 1D 1F 47 FC 5D FE 28 BC 66 4E A5 FA 79 35 E0 06 BD 04 D6 BC C7 3F F6 E0 5D F7 E5 C0 3D 0F 3C DB B9 E1 8D CA 8A E4 7C 5B B3 50 79 F8 8A F7 F3 D7 0F DC F2 9C 0B D2 34 67 D8 A5 3D CE E2 FE E3 21 0F 40 BC E2 B2 F8 DC E3 DE 3A 7F 7D F3 6F D1 CD 6A 2E D6 FD 22 F3 AB 0F DC F2 9C 0B D2 28 F7 85 DE 92 E6 12 F0 F3 CF A2 D3 BB D8 1F 92 28 B5 60 3F B5 D3 E7 DE D7 DD F8 E5 BE 40 37 E7 9F B9 3E F1 C3 35 AB 06 4A DB BF 35 BF 08 EE BA 3D 35 33 5E 29 3B A2 4F 3F 96 FD 7E 18 BA DF 83 AB 0E 45 A1 61 6C 2D CF 91 C8 2B 66 12 E8 DD 37 01 A3 D3 53 35 AF 05 7F 9B 6A 0A A9 78 4E DB 95 A5 B6 50 00 00 00 00 00 00 02 12 BB 40 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 7F FF C4 00 14 01 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 FF DA 00 08 01 02 10 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 0F FF C4 00 14 01 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 FF DA 00 08 01 03 10 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 0F FF C4 00 2D 10 00 02 03 00 03 00 01 02 04 04 07 00 00 00 00 00 05 06 03 04 07 01 02 08 00 10 37 11 20 30 36 16 35 38 70 12 13 14 15 17 34 50 FF DA 00 08 01 01 00 01 08 00 FE F3 3F 70 D9 C2 B1 09 94 B1 7D F8 FB 0B 6F F0 FB 83 33 08 E5 40 24 4E 12 CB F5 8D 73 4C 76 E8 3E 0F D1 65 D5 77 EA 4C 66 AA 8E EF E8 7D 9A 3B BF E8 3B 8E D6 BD 0D 35 FA 71 4F FA 1B C6 C2 F2 86 EB 00 90 43 27 92 C8 EA 53 CB F9 F7 97 AD 05 36 75 FE 8A 08 24 CB 1A 4C 02 48 C7 D5 D2 F1 41 8A 47 AF 08 97 63 F4 44 31 F7 96 51 FE 82 DA CB CF CD 71 B9 1E 87 B1 B1 3A D5 1E D4 B5 B4 3E 93 D9 B8 54 B3 F9 B4 9D 14 3E 6A BF C9 52 12 6F 3B 73 95 D9 7A 2D 0F F4 3E B8 A0 46 38 1B 12 1D 42 3F 00 80 D8 7F D2 23 7E A8 A1 F7 08 DC CD B6 9A FA 63 69 71 83 3F 53 D1 B9 CC AA 2C 90 B8 06 D2 F6 62 DA 5A FA C0 08 B2 B4 91 B9 0A 0C B3 95 2F BF EA 0F 67 BB 8B CF EA 6E BA F6 7A 6A BD 27 D1 C7 A8 1B 58 8C E8 9C 1F 60 7C 79 77 EE 24 F6 F8 E8 C2 88 97 54 A8 0C 7D DD 89 BB 32 BC 7C C0 4F 49 6A 12 F1 76 A7 38 59 BD 74 C9 33 DD 1E BE 8C 5F D4 4C 9F 27 9E 1A B0 4B 3C E6 77 6D 35 FD 96 50 B9 A8 9D DB 4C 40 64 84 3E 99 A0 BF D0 46 4B B0 CD C0 A7 CF 4B 68 31 4E 55 73 23 DD 9D 2E 3A 40 9E E9 A5 E8 43 73 65 99 8C DB 1D A4 7A 3D F7 A4 E4 D6 71 BD F0 E1 E6 3E 8A 2E 7E AA FB 95 53 E5 DD 17 7B 64 0B D0 C2 7E 0B B8 19 79 27 3A E3 26 E1 B8 CB 9F 4D 18 00 36 DF FD 30 AB 43 A3 29 9C B3 4A 1F A6 AE F2 4E BB D6 F6 EE A1 A8 95 19 C6 7C F5 BD 33 BD 03 EC 4F E7 A2 74 C6 FC FA C2 DF 45 DA 7A 3C A1 F1 AA 2F 27 03 BE 7A 3F 4D E9 74 A2 B2 06 DD A4 0E 76 89 49 EF E8 E1 FB 49 8B E7 95 3E E5 5B FA 70 C9 C2 86 D0 50 F7 C6 BD 1B D2 4B F0 C6 C2 53 19 D2 FB 69 8A FD EF 5A D8 37 B3 00 D8 79 50 4A B9 A7 7A 23 39 ED 48 8B 72 2B 90 C7 D5 AA 47 C6 FC F4 B1 DB 46 34 FB 43 79 44 4F 18 8C B0 3C 2D 0D 99 38 6B 7A 01 B8 EC 79 40 FD 8A 8E 25 41 F2 F5 BC 39 A8 6A E4 05 F2 77 47 F4 74 F5 3B B6 D7 C3 B6 6E 74 9A D6 86 95 DC 37 19 73 E9 A3 00 06 DB FF 00 A6 15 68 74 65 33 96 69 43 F4 D5 DE 49 D7 97 D1 DA 10 76 B3 35 AC 65 2E 3B 45 D6 BB 52 BB CD B5 6C DA 49 FB 74 50 38 DB 36 8C E1 86 1A 0E DB B9 A7 0F E0 38 2C A8 65 A5 74 71 25 2F C8 85 A6 6B 10 E6 4A C3 6D 5A A1 A4 7A 4D B6 19 0E 04 C7 7D 07 71 98 CC 2A ED DE 86 D4 1C B3 F2 60 20 5E B7 B3 6E 4D 7C 45 7D 58 6F 5B DD 07 53 EA 43 F3 68 42 07 9D 48 62 A3 7F CD 01 C7 96 D3 A0 ED 77 D1 B3 5A 87 24 39 FE 47 91 AA D1 E0 2B 45 BE 3D 5F 56 8F 74 11 96 65 F3 84 B6 A4 C7 6F F5 9B CB 5C FE 1A 74 9F 3D 5D DF 8E 33 81 FC 7C F3 CF D9 52 7F 3C BF 4A 1B 7A 87 49 64 FA B1 7F 51 32 7C D9 A6 9E 0C B5 BB BC 18 99 3D 34 54 E7 26 42 D0 D6 37 DD 33 B8 DE E6 E0 70 E9 93 63 AB F3 B7 04 D1 B7 7D 06 AF 6E C8 EA 95 8D D4 DE 45 57 39 EB E9 6C FF 00 8D 36 2F 98 8C 55 62 CA 94 F8 AD 50 5E 77 11 FB 73 D5 F5 57 DC AA 9F 16 AA C3 4D 70 35 58 7C F7 C7 11 6D 14 BA 75 D2 67 2D 2E EA 57 BD 53 86 7D 32 C2 18 88 7B DE 6E 43 7A 48 2C C1 C1 FD 12 9C 44 7D 01 76 8C DC 71 C7 1C 7E 1C 7C F5 F7 FD B4 CF 8B 8A 15 9E F0 20 8B B6 3B A0 EE F9 55 89 FB 87 4A F4 E3 40 A2 B1 50 74 E3 9E 39 E3 8E 78 F8 E1 FB 49 8B E7 95 3E E5 5B FA 29 D6 86 DF A3 3A C5 36 F3 0F 4E 72 26 AE 39 F2 1F F2 C6 FF 00 80 2E B5 F1 AD 49 78 0B 8F 7F 47 BC AF 5B 00 5B CE 0A 4D C9 A0 0E 0E 63 F9 E8 6A 16 44 6B A6 2C FC 5E 37 45 90 18 E3 34 35 03 F5 16 90 19 08 59 F2 A8 B9 ED E8 77 2F F1 A6 C5 52 7D FA FC 57 25 86 19 A0 92 09 7C E9 CF 68 76 3A 51 D4 D2 67 2D 2E EA 57 BD 53 86 7D 32 C2 18 88 7B DE 6E 43 7A 48 2C C1 C1 FC FE 94 37 F7 FA 70 4C 54 A5 10 A3 6E 13 BF D3 74 7E 76 39 7A 9E 69 B2 D4 D1 EB 9C 1D 2B FD EF B2 76 BE 79 23 F7 6B 17 CF 55 4D 6B BE 8F 52 29 54 6A D1 A4 AC 0A B5 0D AE 28 C7 6E 44 7B 88 F5 DF F3 B5 4F 98 E5 28 68 65 EA 31 43 F9 D9 A2 96 75 C3 70 C3 E6 C4 F6 D0 5A 14 F6 CB B3 AF 51 6B 5F 26 0A FC 0A FB 36 14 C3 6A D0 52 22 76 DD DC BD 0E A5 53 D4 46 26 AB D0 5D A3 32 96 9B 8A 3D 72 40 43 ED 0D B3 53 5C E0 F9 AC 28 19 A1 19 11 1A 04 BC D2 A0 DA 07 41 B7 6C BF D4 EA 4B 94 DB C4 85 22 30 2E 99 B1 37 C5 DE AA B7 AE 60 8D 56 AE 07 E6 4D E3 62 69 A5 6A 3D CB 33 34 D6 80 2A 90 6C E9 FB 66 52 05 D1 38 50 F4 4D 31 5B 56 12 58 D6 D3 99 F3 A5 AB 75 AB 51 69 AB 76 CA 29 48 B9 06 3F 91 B9 9F 77 E8 F2 EB E9 54 F6 D3 DA 0D 6B 62 04 74 EF 10 91 F1 C9 87 26 38 09 D7 AA DF 25 BF 63 8C 77 D8 7A BA A8 97 D4 F7 66 C0 7C AB F1 89 A7 38 2A AE 4B DD B5 B1 31 C2 CE FF 00 C9 58 3E 9E A6 59 64 61 B4 A7 C8 5A 89 2C 27 F0 C0 40 2A 2A B7 6D B9 07 37 C5 5D E8 89 A5 EE 2F 3C 1B 3F D3 A7 48 FA 75 E9 D3 E3 54 33 58 57 3D 04 1E 6A 50 6D 03 A0 DA B6 5F E2 8A 63 85 6D FB 82 B3 ED 43 EF 95 CB D9 69 0F F2 D2 EB 02 F5 06 AE 86 B5 7C 99 D5 4D EB BB C2 43 0B FE EB A6 D5 AA 06 B6 5E B0 7D 51 4A AD 16 1F 9B 46 45 06 9C 26 09 6A 08 B3 BC E4 12 4C 3E 9D F1 DB AE CD 76 B4 24 72 7C CE 8E 62 B9 CD 0E 9A F0 E9 8C 6D E6 C6 40 75 EF D1 43 04 CA 9D 7B CE D8 F9 55 0E 6C B3 B1 6F D8 E3 1D F6 1E AE AA 25 F5 3D D9 B0 1F 2A FC 62 69 CE 0A AB 92 F7 6D CE 13 1C 28 EE F5 8A 5B 78 5E EC D8 A0 70 17 44 3B BA C6 2C 74 9D 48 74 D5 0D 9D C2 DD 16 B3 80 69 5B 60 CA 29 0C B2 A5 FF 00 2D E4 AC 37 BF DB 36 1C A4 AE A0 AE 08 D5 20 4F DB F2 10 DE 8A D1 64 78 A3 59 A6 BE 8E CF BE A3 57 66 60 2E B5 20 6C DA A5 AA 39 F2 9D 4B 7F FB 6E 89 0E 56 F7 BE E5 AB 7F 7E 3F FF C4 00 45 10 00 03 00 01 03 02 03 03 06 09 09 08 03 00 00 00 01 02 03 04 00 05 11 12 31 06 13 21 14 41 51 10 22 32 55 61 B3 15 20 23 30 54 62 71 72 B2 16 24 33 43 56 70 81 B4 C1 25 34 42 50 53 63 83 92 A2 C2 C3 FF DA 00 08 01 01 00 09 3F 00 FE F9 B2 12 7B AC 13 CE 8A BC D6 82 C1 3B CB 86 F7 B0 ED AB C0 8C C1 D1 87 51 21 1E 8B 8F F8 0F EF EA 9D 18 D8 70 6A 3F C5 8F B9 17 ED 63 E8 35 99 8D 0D B5 1C E4 66 15 C5 43 E4 E3 83 F4 03 7E 6B 6C CC 6C 38 EE 39 33 C6 23 69 2E 0C 92 84 21 07 A3 57 90 CB F3 44 BC 83 80 82 BD 7D 82 74 6B 6B CD 12 7B CC 50 9D A0 8E 14 9F DC FC CE 64 25 8A FB 64 6E 43 C1 28 7A DD DC 68 F2 F4 C7 9B B1 FB 59 41 3F 98 93 B8 C9 4C 93 93 C6 27 B4 7A A1 4E 8D 02 33 F2 30 92 99 00 A7 96 43 9E FF 00 37 F1 11 9F 3E 1B 75 E9 8A AA 9E 61 35 54 25 40 4F 7E B6 DC C4 44 52 CE ED B4 10 00 FF 00 D3 54 4C BB 04 2E 67 0C 05 AB 71 FB 10 6B 03 2A 5B 6B C2 E5 DD F6 E3 8E 3A 95 39 5F 9F C6 B3 71 CE D9 F8 73 33 17 A0 41 03 79 52 2E 07 E3 A9 B5 68 FE 5E 2E 2A 1E 1E D4 FF 00 45 1E F3 A8 3C C0 3F D0 6D D8 03 24 A8 3F 12 EB 4D 61 7B 4A F7 7C 7C CC 4F 63 B1 5F 8A 14 54 D5 49 93 92 94 93 FA 52 34 1D D1 C7 E6 DC A6 3E 2E 3D 2F 67 E0 B7 4C E4 A5 98 F0 3D 4F 00 6B 6E 68 6D D8 78 5E 6A 5A C7 F2 D5 62 E1 7B 0F 45 1F 9D 46 96 1E 7E 47 5D 0C FD 3D 9F 33 BF FF 00 3D 42 8B 54 44 6C F5 41 FE F3 99 C9 44 E8 03 4F 39 65 34 4E 6E EB 90 7B 27 4A F3 D1 FB 26 35 80 F8 F2 F5 32 49 41 72 32 5D 07 77 A1 70 55 75 B7 3E 4C 48 E5 A5 78 24 2C 53 E3 2A 4C 00 75 71 4C 7C 8C 23 78 3F F8 76 23 E2 0F A1 1A DC 25 6C 51 B7 5A C1 16 13 99 EB 42 BE F4 1A C9 48 65 3E EB 18 17 69 AD 07 43 CD DB B3 EB 25 2B 9D 3B 66 2A D0 4D 10 71 24 05 7D 17 45 33 F3 B2 64 23 80 89 88 9C A5 4B 0E 5F 89 8E 5C E8 E5 26 36 34 A5 E5 4F 2B 0D 71 A8 6B 53 DD 38 54 E5 40 1F 2F F6 C6 1F E6 17 54 59 CA 68 CF 47 63 C2 AA A8 E4 92 7E 03 58 46 51 01 FC B2 92 4A DA 88 BD EA E6 C3 89 8D 60 1A 45 C2 F5 B1 92 4E C8 87 FA D4 31 F9 94 1A 41 94 0A 4C 62 22 9F 4B 3D BE 86 83 8C 21 42 00 C7 86 34 A4 08 F7 21 BF AB E9 12 CF 7B BE 30 B1 88 85 E1 74 F7 38 4D 4F CF A9 71 2C 5C 60 DD 26 D5 6D 42 8B 85 2A 1F 4C 5C 58 09 FE E0 37 04 BE A3 31 9B 57 79 43 24 4F C9 7F 39 3B CA C9 AF A9 31 FE F6 9A D9 6F 83 B0 E3 63 81 3A A4 A3 5A D5 60 38 2F F9 5E 4B 7E C4 1A 11 7C D1 06 BE 36 54 D0 4F CC 09 DD 1D 46 A3 1B 6E F5 88 A5 2B 4F 54 C6 46 ED CA FB DC EB 1A A7 6C 72 8C 45 F1 21 D0 A1 BB 07 59 00 F3 D4 0E 3E 4C 29 E4 E5 E3 13 D5 E5 BF 7E 41 F7 A9 D6 54 EB B4 61 67 28 38 A2 12 0C F3 08 09 4E BD 47 2B 17 65 CF B1 77 0F B7 09 E3 79 08 0B 90 8E 53 E4 CD 9C 06 64 F2 CD FA E2 95 E4 C8 A7 1F 4F 5F CE 32 3F 06 4A CE 10 04 F3 6D 52 11 46 BC A8 E0 C6 C5 3A 20 98 D3 40 E0 73 D0 0E 4F 2C 75 82 F9 04 E4 2C 2C E3 1C 25 F1 9D FB 39 F2 40 53 3F 97 EA 9C BF BA 6D 7D 47 91 F7 B2 F9 31 5B 24 E1 F8 83 70 75 82 9E 0D 18 BB A8 1A C5 6D B3 6D 7A 70 93 18 B0 69 27 5F 60 E0 87 71 A8 24 73 F1 2F E4 65 A2 7D 02 78 E4 51 75 8E 95 DC 52 A9 2B E4 F4 79 E7 CE 7E D1 94 FD EF AC 33 6C 0B DB B6 44 60 14 FC 53 AF 1F D5 0E 83 24 EE 08 79 37 D2 95 10 F0 C8 7E 47 63 1D B3 1E 18 F1 4F 77 35 41 67 3A 8A 2F 95 15 F3 E8 14 06 B5 B8 F9 F4 7D 41 0E 56 1E 1D 72 F0 ED C0 2E 94 8A 97 E0 1F 83 F1 C1 D3 9F 67 CD DB CD BA 7E 15 83 6B 30 3E C9 85 9F 11 4C 64 84 8D 1A 3D 0A EE 81 CE B6 FC 8D BB 64 E3 CD 9C D3 16 2E 89 1F 8B 8A 02 FA 94 A3 BC 61 C8 3B 74 7A 26 44 BB 1A 28 D4 63 6D DE B1 14 A5 69 EA 98 C8 DD B9 5F 7B 9D 63 54 ED 8E 51 88 BE 24 3A 14 37 60 EB 20 1E 7A 81 C7 C9 85 3C 9C BC 62 7A BC B7 EF C8 3E F5 3A B4 73 31 A3 91 99 0C 6C 6F 21 17 97 04 A4 B9 28 01 D1 CC C6 D9 63 B6 5F 3A E3 2B 00 63 8E 84 EC 24 42 2E B1 0E 3E 3C F9 29 29 46 2F 41 2E C1 AD 4B F2 01 D4 FD A1 78 47 A6 2D E5 14 73 23 EF 95 61 A8 D6 D8 B9 B0 B7 B7 95 88 A7 18 54 81 25 8F 3D B5 8B 6B E5 BE 28 19 02 70 5B 91 2E AF 83 6A 03 23 79 CD 88 10 C5 27 A0 75 85 05 DE 9F 04 5D 4B 29 B0 51 8F 03 1B 06 26 3F B1 3A D4 B3 EA 12 8E 7D 5C A6 3E 5A 0F 28 3D 07 F5 54 4F 73 EB 3A 70 4C AC 6B 3D 83 C5 2B C9 46 00 7D 3D 61 67 4F 6F C6 48 C9 E9 8F 80 B6 5A 54 00 1D A8 E5 18 6A 89 4C B1 8F 31 90 E8 3A 55 AA 14 75 95 1E E0 4F E3 C4 56 2D B7 5D F8 3E E7 9A 97 46 1F 68 23 50 15 F6 3C 1B 65 40 1E C2 C8 55 43 68 90 1E B8 A9 5F DC 36 5D 05 39 67 32 32 73 EF 12 09 C8 D0 5F 69 96 F1 34 83 7B F8 79 3F 58 D0 F9 89 9B 9C B0 FD C2 80 EB EA 7C 9F E2 4D 7B F7 E8 7D CD 75 FA 46 E1 F7 63 48 09 C5 DB 32 6D 3F B1 8F 13 FC 4F ED 8C 3F CC 2E 87 2C 76 D7 43 FB 8E 42 BE B6 3C 4D C2 AC 98 EB 96 6E 01 32 5E 5C A7 1C D2 7A F0 64 11 F0 85 44 9B 18 CD 09 15 E3 90 DC D5 B5 89 41 9D 0C 34 C3 4C 1E 41 7A 55 01 0A 9C 8E 40 1C 0D 78 67 6E DB 36 DF 31 C2 DD 26 81 41 F7 FC FB 9E 1B 59 2B 7D CD 3C 48 83 32 A8 79 0F 50 FF 00 3F 5E 90 E3 38 FE D7 FC 96 80 08 70 7A 9B 8F 7B 97 25 F5 89 B0 FE 19 37 EB BB AA C4 E5 0A 9F 5E 58 FD 30 75 F5 26 3F DE D3 48 16 52 DB F1 D1 54 76 01 66 06 BB 05 CF FB A6 D6 34 EF 9C 37 E8 2E 2C 29 F4 2A E8 51 64 8D AF 04 6D C7 1B 37 16 90 AF 01 01 0B 41 C1 23 9B EB 68 AE 1E 2E 5E 24 7A 0B 3A 30 6A 49 FF 00 50 9D 28 69 E4 78 87 0E 2E 3E 22 85 17 E5 FF 00 A5 9F FF 00 E5 AB 18 8C AD 9E 1D 15 03 93 3A 4C 87 46 D4 B7 31 0E 79 35 DB 1C E4 46 9F 6B 4C 7F F6 5D 61 47 23 1C D1 52 F9 0B 1F 23 26 5E E2 EC A3 D1 B4 79 07 E4 FA A7 2F EE 9B 5F 51 E4 7D EC BE 44 0C A3 C5 79 AF FE 33 AB B8 D0 ED 08 7D FA 6B F4 9C 3F E1 7D 60 47 33 7C FC 2B 9D 58 E3 5B 82 8D 5F 9E 5B BB 27 6D 78 2B 04 62 E4 19 92 64 11 5C 19 B8 70 54 9B 9D 6D B4 C3 EB CF 4B C1 1D D1 B9 E5 38 7F A0 4F C9 E8 32 46 2E 5C 0F FE 30 9F C4 9A A0 7C 7C CC 64 B2 11 FA C3 D4 1F B4 1F 43 AA 84 FF 00 67 5A 31 F8 B5 AC A6 73 5D 03 E5 61 ED 55 2E DF 6D 58 20 1A 00 E3 3E F9 84 B6 07 B1 99 13 0D A9 AB 49 D0 A3 A3 0E 54 A9 1C 10 47 C3 5E B0 31 CE 46 3F F6 84 CE B1 A7 7C E1 BF 41 71 61 4F A1 57 42 8B 24 6D 78 23 6E 38 D9 B8 B4 85 78 08 08 5A 0E 09 1C DF 5B 45 70 F1 72 F1 23 D0 59 D1 83 52 4F FA 84 E9 03 20 F1 0E 65 78 3F 18 97 A8 D5 84 B1 71 60 F6 B5 0F AF 4A 20 E4 9D 78 37 17 F5 F2 6A 81 EC 50 76 7A 1E 51 13 59 71 B6 7D B0 BA E2 92 29 C4 A3 D6 47 49 F2 80 1A FE C5 BF F9 3D 7D 54 3E F4 68 9F 29 36 68 79 23 F6 BB E9 54 62 A6 DD 8E 22 17 B1 5E 81 C1 D2 28 BF B6 60 D8 22 FE 92 C8 8F AF D0 B2 7F 8C 69 02 87 DB 25 63 F6 B5 FF 00 28 4F E6 26 CF 47 DB B2 55 11 41 2C CC 66 40 00 0D 78 77 76 C1 81 DA 2E 82 D9 58 95 8A 16 2E 9C 0E 5C 6B 9F 67 CD C7 69 39 5E EB CF 66 1F 6A 9F 51 AC 0B 65 E3 50 74 1B 42 0D 93 8D 91 31 DB CC 55 F5 43 AD AE B8 78 71 E7 A1 EB 8E F8 98 71 0D DD C7 5F 25 CE 87 30 C7 89 47 73 DE AE FE AE E7 ED 63 AD A7 26 EB 0A D1 71 72 52 0F 7C 7C 88 3F B9 FA 35 B1 DE 18 98 76 41 87 B5 62 E2 DB CD B3 57 BD 7C AF 57 D6 D7 9B 89 96 D7 CE 23 1E F0 79 54 87 40 07 08 FA F0 F6 EB 83 03 B3 DD 05 72 B1 2B 14 2E 69 33 C7 2E 3F 13 C3 3B C3 E0 9F 15 C2 DE D4 30 AC 63 E5 0B 82 5F AF 8E 38 D4 FA F1 B2 F1 E9 0A AF C5 28 3A 4E B6 AA E7 E1 D0 19 1A CE 0F 7C 6C 99 77 1D 62 7E A8 FA 86 7E C9 09 01 31 78 8B 61 63 C2 64 F2 5F 92 79 A3 6A 96 CE CF DA 1D 1C 0B 3F 36 CA 40 9D 0E 79 6E F4 D7 81 B2 72 5E 4E FE CC F7 C3 BA 18 79 CC 58 F5 EB 60 DD 73 DE 7B B6 2E 6E 66 66 26 35 72 64 FE 71 14 A1 0E 8B C1 23 4F 39 EE 78 75 36 C2 77 3C 21 27 D1 E6 DF 63 6B C3 99 4D 01 57 F2 53 23 06 B7 54 63 DC C5 E7 AC 5B E3 A4 B2 8E 68 5C A4 E8 B6 4E 4F 70 7A 3B A2 AE BC 3B BA E7 40 6C F0 43 5C 5C 4A D9 03 8A 50 F1 CA 0D 29 57 5C 48 86 52 38 20 84 1C 83 AF 0D EE F8 98 83 DB B9 C8 BE 1D 65 21 D7 36 03 97 71 AC 6A 5E CE 26 72 E1 0F E9 D6 B1 ED 69 EB C3 59 53 BD 94 4A F9 38 F8 37 9E 43 FF 00 A2 6B 77 CD CA CF CB 75 61 8B 6C 97 BA E2 4D 7B 20 E4 91 D4 75 E1 BD DE 98 1F CA 4C 1A FB 5A 61 D8 C3 A1 1D 39 7E B0 3E 5D 93 71 CF 11 96 68 A9 C4 C6 A5 FA 39 33 EF D1 AC DC DD 9F 77 8E DD 06 4F 9D 5C 67 15 4E F2 B0 1C 1E 0E BC 2D 9B 9F 0A DB CD FE 71 2B 5C 0A 76 E6 76 96 B6 3A 6D 38 4C 64 97 B3 C1 A0 89 09 FB A6 2B EB 47 D0 01 54 00 00 F7 01 F2 4D E9 5A 6D 99 48 88 80 B3 33 34 88 00 01 DC 9D 78 77 75 C1 81 D9 EE 82 B9 58 95 8A 17 34 99 E3 97 1F 27 86 F7 79 E0 7F 28 B3 AB ED 6F 87 61 1E 87 34 E1 FA C8 D6 25 F2 B2 6B 09 09 C2 13 35 A3 91 64 3E 8A BA D9 F3 F0 0D 6F 88 64 32 F1 DE 05 F8 0F DB AF 58 97 BC 5F 2F DB 00 C5 4E BA E3 5F BB F3 3F 7A 36 BC 37 97 87 C5 91 A8 F8 58 D7 81 77 4E DD 75 A1 E1 17 5B C6 4E E3 B9 3B 1A DE 96 BB DC 4C B7 69 21 7F 72 FC 95 48 6F 18 41 BD 96 AF F4 28 87 BC A9 AC 0D CE 38 C5 C9 32 38 DE D7 8A 4F C5 18 07 1A C1 DC 6B 19 B7 CC F3 A1 EC 78 92 FB 7B 28 D5 45 F3 B2 5C 57 3B 24 0E 03 B8 EC AB F0 44 D3 AA 57 2F 75 C6 C7 46 6E C1 AA 88 9A D8 B2 4D 4C CE 37 B7 47 0A 96 BD 93 B7 CC AA 72 87 58 FE 46 7E 44 3C 9C 4C 63 F4 E3 26 F5 73 4F 83 B6 B1 A9 7B 38 99 CB 84 3F A7 5A C7 B5 A7 AF 0D 65 4E F6 51 2B E4 E3 E0 DE 79 0F FE 89 AD DF 37 2B 3F 2D D5 86 2D B2 5E EB 89 35 EC 83 92 47 51 D7 86 F7 78 60 8D DB 71 73 95 5C 3B 24 42 BA 57 83 D6 46 AA 24 F9 B8 54 94 DC F6 57 23 95 27 5E 0D CC CA 39 81 27 58 1C 7A D1 5C CB 92 8D 2A 4B 5E 1C CB A5 33 11 A5 1C 1C 38 52 CF 89 19 76 0E 88 18 A0 62 DA C6 BE 16 4E 57 87 3D 8A B2 C9 9B 49 E5 43 0F 24 F5 2B 6B C3 39 83 36 D1 38 AE 94 C2 A5 D1 87 20 F3 32 9A 98 97 88 31 70 50 D3 1E BF 93 F3 45 14 3B C8 FC 1D 5B 5E 1F CC 71 11 E5 63 79 FB 75 6D 49 0F 84 99 3D 18 6A 35 90 4C A3 94 21 93 E9 91 91 91 DC 3B A7 B9 01 D6 C7 B9 67 A4 B1 2E 28 71 31 69 70 84 B8 EE 50 6A 15 85 E3 B3 E2 25 65 54 28 E8 EB 30 0A B2 9E C7 FE 79 E1 CD D6 B8 1F 87 F0 2B ED 49 8B 43 1E 84 33 E5 BA FF 00 BF 9F FF C4 00 14 11 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 70 FF DA 00 08 01 02 01 01 3F 00 64 FF C4 00 14 11 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 70 FF DA 00 08 01 03 01 01 3F 00 64 FF D9",
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
