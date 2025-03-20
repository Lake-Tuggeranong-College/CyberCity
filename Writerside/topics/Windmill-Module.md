# Windmill Module

This is a sample document to show how to and what to write for the configration of a module.


## Wiring Diagram

![Windmill Module Wiring Diagram](wiringWindmill.png)

|Module Pin| Adafruit ESP32 Feather pin|
|--|--|
| VCC | 3.3V|
|GND | GND|
|Signal | GPIO21|


## Data Transport Method

MQTT or HTTP/PHP.


## Data Format

The data format is JSON. The data will be sent in the following format:

```json
{
  "wind_speed": 12.5,
  "wind_direction": 180
}
```


## Data Transmitted

The ESP32 will upload the current temperature to the server.

The ESP32 will receive a payload from the server to control whether the windmill will spin or not, using the servo.


## Code

```c++
#define AIN_PIN A2  // Arduino pin that connects to AOUT pin of moisture sensor
#include <ArduinoJson.h>
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <Arduino.h>
CyberCitySharedFunctionality cyberCity;

#include <ESP32Servo.h>
// Declare the Servo pin
int servoPin = 21;
// Create a servo object
Servo Servo1;
String outputCommand = "NaN";

// MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

// Declare the callback function prototype before setup()
void callback(char* topic, byte* payload, unsigned int length);


void setup() {
  Serial.begin(9600);
  // We need to attach the servo to the used pin number
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);
  Servo1.setPeriodHertz(50);  // standard 50 hz servo
  Servo1.attach(servoPin);
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
  pinMode(LED_BUILTIN, OUTPUT);

  // Setting up MQTT
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);  // Set the callback function to handle incoming messages

  // RTC
  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
    Serial.flush();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.start();

  //EINK
  display.begin();
  display.clearBuffer();

    // Connecting to MQTT Broker
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");

    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe("RegisteredModules/Servo");  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);
    }
  }

  cyberCity.logEvent("System Initialisation...");
}

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();

  // Example: turn on/off an LED based on the message received
  // if ((char)payload[0] == '1') {
  //   Serial.println("spin please");
  //   Servo1.write(0);
  //   outputCommand = "Fan On";
  // } else {
  //   outputCommand = "Fan Off";
  //   Servo1.write(90);
  //   Serial.println("not spinnin");
  // }

  if ((char)payload[0]) {
    Serial.println("spin please");
    Servo1.write(0);
    outputCommand = "Fan On";
    delay(5000);
    Servo1.write(90);
  } 
}

void loop() {
  /*int value = analogRead(AIN_PIN);  // read the analog value from sensor

  Serial.println(value);
  int sensorData = value * 1.0;
  cyberCity.updateEPD("Farm", "value", sensorData, outputCommand);
  String dataToPost = String(value);
  // cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 40, serverName, true, true);
  //notes need to // the next to line 
//  int payloadLocation = payload.indexOf("Payload:");
 //char serverCommand = payload.charAt(payloadLocation + 8);

 Serial.print("Payload from server:");
  Serial.println(payload);
  DynamicJsonDocument doc(1024);
//  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    return;
  }
  const char* command = doc["command"];
  Serial.print("Command: ");
  Serial.println(command);
  
  
  if (String(command) == "On") {
    Serial.println("spin:)");
    Servo1.write(0);
   outputCommand = "Fan On";
    
 } else {
   outputCommand = "Fan Off";
    Servo1.write(90);
  }*/

    if (!client.connected()) {
    while (!client.connected()) {
      Serial.println("Reconnecting to MQTT...");

      if (client.connect("ESP32_Client")) {
        Serial.println("Reconnected to MQTT");
        client.subscribe("RegisteredModules/Servo");
        Serial.println("Connected to topic");
      } else {
        Serial.print("Failed to reconnect, state ");
        Serial.print(client.state());
        delay(2000);
      }
    }
  }
  client.loop();  // Check for incoming messages and keep the connection alive
}


```