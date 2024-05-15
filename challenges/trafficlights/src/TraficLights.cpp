#include <CyberCitySharedFunctionality.h>
#include <ArduinoJson.h>
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/

#define TRIG_PIN 	19
#define ECHO_PIN 18

int green = 15;
int red = 16;
int yellow = 17;

#include "sensitiveInformation.h"

CyberCitySharedFunctionality cyberCity;


void lightsOn()
{
  digitalWrite(red, HIGH);
  delay(15000);
  digitalWrite(red, LOW);

  digitalWrite(yellow, HIGH);
  delay(1000);
  digitalWrite(yellow, LOW);
  delay(500);

  digitalWrite(yellow, HIGH);
  delay(1000);
  digitalWrite(yellow, LOW);
  delay(500);

  digitalWrite(green, HIGH);
  delay(2000);
  digitalWrite(green, LOW);
}

void lightsOff()
{
  digitalWrite(red, LOW);
  digitalWrite(yellow, LOW);
  digitalWrite(green, LOW);
}

void sonarSensorData()
{
  
  // this sets up the distance that sonar will be trigger when a object comes too close. 
  // if something is too close then return true, else return false 
  
  int duration; // variable for the duration of sound wave travel
  int distanceSensorData; // variable for the distanceSensorData measurement
   // Clears the trigPin condition
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  // Sets the trigPin HIGH (ACTIVE) for 10 microseconds
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  // Reads the echoPin, returns the sound wave travel time in microseconds
  duration = pulseIn(ECHO_PIN, HIGH);
  // Calculating the distanceSensorData
  distanceSensorData = duration * 0.034 / 2; // Speed of sound wave divided by 2 (go and back)
  // Displays the distanceSensorData on the Serial Monitor
  Serial.print("Distance: ");
  Serial.print(distanceSensorData);
  Serial.println(" cm");
  Serial.print("Payload from server:");
  String dataToPost = String(distanceSensorData);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 300, serverName, true, true);
  Serial.print("Payload from server:");
  Serial.println(payload);
  DynamicJsonDocument doc(1024);
  //  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error)
  {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    return;
  }
  const char *command = doc["command"];
  Serial.print("Command: ");
  Serial.print(command);
  delay(500);
  if (String(command) == "On")
  {
    Serial.println("spin:)");
    lightsOn();
    // outputCommand = "Fan On";
  }
  else
  {
    // outputCommand = "Fan Off";
    lightsOff();
  }
}

void setup()
{
  /*
   */

  pinMode(ECHO_PIN, INPUT);
  pinMode(TRIG_PIN, OUTPUT);

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
  pinMode(LED_BUILTIN, OUTPUT);

  // RTC
  if (!rtc.begin())
  {
    Serial.println("Couldn't find RTC");
    Serial.flush();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.start();

  // EINK
  display.begin();
  display.clearBuffer();

  cyberCity.logEvent("System Initialisation...");

  // Module Specific Code

  // put your setup code here, to run once:
  pinMode(green, OUTPUT);
  pinMode(red, OUTPUT);
  pinMode(yellow, OUTPUT);
  lightsOff();
  Serial.begin(9600);
}

void loop()
{

  // put your main code here, to run repeatedly:
  // int sensorData = red, green, yellow;
  sonarSensorData(); // this function runs both sonarSensorData and Lights on and Off
}

