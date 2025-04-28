#include <CyberCitySharedFunctionality.h>
#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include "sensitiveInformation.h"
#include <ESP32Servo.h>
CyberCitySharedFunctionality cyberCity;
// Declare the Servo pin
int servoPin = 14;
// Create a servo object
Servo Servo1;
String outputCommand = "NaN";

// MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

void mqttConnect() {
// Connecting to MQTT Broker
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe(mqttTopic);  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);
    }
  }

}



void setup()
{

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

  // Setting up MQTT
  client.setServer(mqttServer, mqttPort);

  mqttConnect();
 
}

void mqttLoop() {
   if (!client.connected()) {
      mqttConnect();
    }
  client.loop();  // Check for incoming messages and keep the connection alive
}

void loop()
{
  mqttLoop();
}
