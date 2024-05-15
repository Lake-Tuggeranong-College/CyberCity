
/*************************************************** Guess the Pass Key ESP32 feather Model Code ****************************************************/
/*
 * This file is the code for Guess The Pass Key challenge Model
 * that sends Emails to the Database Backend of the School based PHP web sever
 * to provide hints to the user around which word is the pass key.
 */
/*************************************************** Guss the Pass Key ESP32 feather Model Code ****************************************************/
#include <Arduino.h>
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;
#include <Wire.h>
#include <WiFi.h>
#include <ArduinoJson.h>
#include "Adafruit_ADT7410.h"

String Email_Selector_Array [6] =  // This Array holds the Simulated Emails that will be sent to the Data base and be posted on the challenge website
{
  "Xen.Cr: 'Hey John.R how many vowels did we want the Key to have?' John.R: they have said to have 2 vowels", // Email 1
  "John.R: 'Don't Forget to have no repeating charters Xen'", // Email 2
  "Jay.P:  'I am Happy to report that the system is Very Strong and unlikely or anyone to Break in", // Email 3
  "Xen.Cr: 'The Spelling doesn't look right... where is o meant to be? Jay.P: 5th from the right", // Email 4
  "Ben.W:  'John Please Help. I can't remember What the 'thing' ended with. Was it Ending with Two ss or T.' John.R: I Don't think it was ss, Try T", // Email 5
  "jay.P:  'I want the word to be the same amount of charters and starts with the same charter in Upper case as Roband" // Email 6
};
int Last_Sent_email; // This will hold the Most recently number picked To stop a repeated email being Sent
bool Recently_Sented_Email = false; // This will aid in stopping a repeated Email with an if Statement

int Email_Selector_Array_Size = sizeof(Email_Selector_Array)/sizeof(Email_Selector_Array[0]);
// This Finds how big the Array is to Identify how many Emails are in the Array So it can latter Chooses a random Email to send

void Send_The_Email(String Selected_Email) // This Function Grabs the Selected Email, turns it into A JSON Object to be Sent to the Back-end DataBase of the PHP webBase sever
{
      // Turns the Selected Email into the JOSN Object to send  
  String dataToPost = String(Selected_Email);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 1500, serverName, true, true);
  Serial.print("payload: ");
  Serial.print(payload);
  Serial.println(".");
  DynamicJsonDocument doc(1024);
 
  //  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error) // Should the Email not Reach the DataBase/ Web Base Sever Resend it
  {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    Send_The_Email(Selected_Email);

    if (WiFi.status() != WL_CONNECTED) // Should the Wi-fi Drop/Disconnect then Start restart the wi-if connection
    {
      WiFi.begin(ssid, password);
    }
    return;
  } 
}

int Pick_New_Email()
/*
This Function Generates a Random int in the range of the size of the Email array,
then Checks to See if that Email has been recently Selected, 
if not recently Selected the it will Call the Send_The_Email() function,
else Restart this Function     
*/ 
{
  randomSeed(analogRead(A4)); // Generate a random int
  int RandNumberGen = random(Email_Selector_Array_Size); // Grab the Email within the array that corresponds to the int number Generated

  if (RandNumberGen != Last_Sent_email) // checks if the Email was sent recently
  {
    // send the Email Selected
    Last_Sent_email = RandNumberGen;
    String Selected_Email = Email_Selector_Array[RandNumberGen];
    Send_The_Email(Selected_Email);
    delay(60000);
  }
  else
  {
    //Restarts the function
    Pick_New_Email();
  }
}

void setup() // This begins Serial, Starts wi-fi Connections and Creates the randomising seed
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
}

void loop() 
{
  
  Pick_New_Email(); // Starts The Email Selection Function

}