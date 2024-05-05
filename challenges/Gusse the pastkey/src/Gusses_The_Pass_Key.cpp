
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/


#include <Arduino.h>
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;
#include <Wire.h>
#include <WiFi.h>
#include <ArduinoJson.h>
#include "Adafruit_ADT7410.h"
//#define display
//#define clear

//RTC_DS3231 rtc;

String outputCommand = "nill"; // beucase this Model Dose not uses Commands This is set to 

String Email_Selector_Array [6] = 
{
  "Email_0: Xen.Cr: 'Hey John.R how many vowels did we want the Key to have?' John.R: they have said to have 2 vowels",
  "Email_1: John.R: 'Don't Forget to have no repeatting charaters Xen'", 
  "Email_2: Jay.P:  'I am Happy to report that the system is Very Strong and unlikely or anyone to Break in",
  "Email_3: Xen.Cr: 'The Spelling dosn't look right... where is o meant to be? Jay.P: 5th from the right",
  "Email_4: Ben.W:  'John Please Help. I can't remember What the 'thing' ended with. Was it Ending with Two ss or T.' John.R: I Don't think it was ss, Try T",
  "Email_5: jay.P:  'I want the word to be the same amount of charaters and starts with the same charater as roband"
};



int Email_Selector_Array_Size = sizeof(Email_Selector_Array)/sizeof(Email_Selector_Array[0]);

String  Email_Selector_data;

void Send_The_Email(String Selected_Email) 
{
      
  String dataToPost = String(Selected_Email);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 1500, serverName, true, true);
  Serial.print("payload: ");
  Serial.print(payload);
  Serial.println(".");
  DynamicJsonDocument doc(1024);
 
  //  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    Send_The_Email(Selected_Email);

    if (WiFi.status() != WL_CONNECTED)
    {
      WiFi.begin(ssid, password);
    }
    return;
    
  } 
}

void setup() 
{

  Serial.begin(9600);
  while (!Serial) {
    delay(10);
  }
  delay(1000);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  Serial.println();
  Serial.print("Connected to the Internet");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  //EINK
  //display.begin();
  //display.clearBuffer();

  //cyberCity.logEvent("System Initialisation...");
  randomSeed(analogRead(A4));
}

void loop() {

  randomSeed(analogRead(A4));
  int RandNumberGen = random(4);
  Email_Selector_data = Email_Selector_Array[RandNumberGen];
  String Selected_Email = Email_Selector_data;
  Send_The_Email(Selected_Email);
  

  display.clearBuffer();
  delay(600000);
}

