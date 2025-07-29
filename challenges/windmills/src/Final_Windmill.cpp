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
      client.subscribe("Challenges/Windmill");  // Subscribe to the control topic
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


  if ((char)payload[0] == '1') {
    Serial.println("spin please");
    Servo1.write(180); // 180 = full speed
    outputCommand = "Fan On";
    delay(5000);
  }
  if ((char)payload[0] == '2') {
    Serial.println("no spin");
    Servo1.write(90); // 90 = stopped 
    outputCommand = "Fan Off";
    delay(5000);
  }   

}

void loop() {
   if (!client.connected()) {
    while (!client.connected()) {
      Serial.println("Reconnecting to MQTT...");

      if (client.connect("ESP32_Client")) {
        Serial.println("Reconnected to MQTT");
        client.subscribe("Challenges/Windmill");
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
