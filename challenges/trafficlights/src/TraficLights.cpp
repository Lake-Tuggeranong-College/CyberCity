#include <CyberCitySharedFunctionality.h>
//#include <ArduinoJson.h>
// REQUIRED LIBRARIES, DONT REMOVE
#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/

#define TRIG_PIN 13
#define ECHO_PIN 12

int tl1Green = 15;
int tl1Red = 16;
int tl1Yellow = 17;
int tl2Green = 23;  // ESP32";";ix
int tl2Red = 14;    // Fix
int tl2Yellow = 22; // Fix

#include "sensitiveInformation.h"

CyberCitySharedFunctionality cyberCity;

void lightsNormal()
{
  // First direction (tl1)
  digitalWrite(tl1Green, HIGH);   // tl1 Green
  digitalWrite(tl1Yellow, LOW);
  digitalWrite(tl1Red, LOW);
  digitalWrite(tl2Green, LOW);    // tl2 Red
  digitalWrite(tl2Yellow, LOW);
  digitalWrite(tl2Red, HIGH);
  delay(5000); // Green for 5 seconds

  // Transition to yellow for tl1 and red for tl2
  digitalWrite(tl1Green, LOW);
  digitalWrite(tl1Yellow, HIGH);  // tl1 Yellow
  digitalWrite(tl1Red, LOW);
  digitalWrite(tl2Green, LOW);    // tl2 Red stays
  digitalWrite(tl2Yellow, LOW);
  digitalWrite(tl2Red, HIGH);
  delay(1000); // Yellow for 1 second

  // Switch to red for tl1 and green for tl2
  digitalWrite(tl1Yellow, LOW);
  digitalWrite(tl1Red, HIGH);    // tl1 Red
  digitalWrite(tl2Green, HIGH);  // tl2 Green
  digitalWrite(tl2Yellow, LOW);
  digitalWrite(tl2Red, LOW);
  delay(5000); // Green for 5 seconds

  // Transition to yellow for tl2 and red for tl1
  digitalWrite(tl1Red, HIGH);    // tl1 Red
  digitalWrite(tl2Green, LOW);   // tl2 Green off
  digitalWrite(tl2Yellow, HIGH); // tl2 Yellow
  delay(1000); // Yellow for 1 second

  // tl1 stays red and tl2 switches to red
  digitalWrite(tl2Yellow, LOW);  // tl2 Yellow off
  digitalWrite(tl2Red, HIGH);    // tl2 Red on
  delay(500); // Brief pause before next cycle
}


void lightsChaos()
{
  //all lights off
  digitalWrite(tl1Green, LOW);
  digitalWrite(tl2Green, LOW);
  digitalWrite(tl1Yellow, LOW);
  digitalWrite(tl1Red, LOW);
  digitalWrite(tl2Red, LOW);
  digitalWrite(tl2Green, LOW);
  digitalWrite(tl2Yellow, LOW); 
  delay(1000);
  //all lights on
  digitalWrite(tl1Green, HIGH);
  digitalWrite(tl2Green, HIGH);
  digitalWrite(tl1Yellow, HIGH);
  digitalWrite(tl1Red, HIGH);
  digitalWrite(tl2Red, HIGH);
  digitalWrite(tl2Green, HIGH);
  digitalWrite(tl2Yellow, HIGH); 

  delay(1000);
}


// Declare the callback function prototype before setup()
void callback(char* topic, byte* payload, unsigned int length);
// MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

void sonarSensorData()
{

  float duration, distance;
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
}

void mqttConnect() {
// Connecting to MQTT Broker
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe("Challenges/TrafficLights");  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(1000);
    }
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

  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.start();

  // EINK
  display.begin();
  display.clearBuffer();

  cyberCity.logEvent("System Initialisation...");

  pinMode(tl1Green, OUTPUT);
  pinMode(tl1Red, OUTPUT);
  pinMode(tl1Yellow, OUTPUT);
  pinMode(tl2Green, OUTPUT);
  pinMode(tl2Red, OUTPUT);
  pinMode(tl2Yellow, OUTPUT);



  // Setting up MQTT
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);  // Set the callback function to handle incoming messages

  mqttConnect();
 
}

void mqttLoop() {
   if (!client.connected()) {
      mqttConnect();
    }
  client.loop(); // Check for incoming messages and keep the connection alive
}


void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();


  if ((char)payload[0] == '2') {
    Serial.println("CHAOSSSS");
    lightsChaos();
  }
  if ((char)payload[0] == '1') {
    Serial.println("You Fixed It!");
    lightsNormal();
  }   

}

void loop()
{

  // put your main code here, to run repeatedly:
  // int sensorData = red, green, yellow;
  sonarSensorData(); // this function runs both sonarSensorData and Lights on and Off
  mqttLoop();
}
