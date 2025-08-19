/*
 * ESP32 Garage Door Control System
 * This code controls a garage door servo motor via MQTT commands
 * The system connects to WiFi, subscribes to MQTT topics, and controls
 * a servo motor to simulate garage door opening and closing operations
 * Features:
 * - Remote control via MQTT messages
 * - Real-time clock integration
 * - E-Ink display support
 * - Event logging system
 */

// Define analog input pin for moisture sensor (Note: Not currently used in garage door logic)
#define AIN_PIN A2  // Arduino pin that connects to AOUT pin of moisture sensor

// Include necessary libraries
#include <ArduinoJson.h>                      // JSON parsing library for data handling
#include "sensitiveInformation.h"             // Contains WiFi credentials and MQTT settings
#include <CyberCitySharedFunctionality.h>     // Custom library for shared project functionality
#include <WiFi.h>                             // WiFi connectivity library
#include <PubSubClient.h>                     // MQTT client library
#include <Arduino.h>                          // Core Arduino functions

// Create instance of shared functionality class
CyberCitySharedFunctionality cyberCity;

// Include ESP32 servo library for motor control
#include <ESP32Servo.h>

// Servo motor configuration for garage door mechanism
int servoPin = 14;               // GPIO pin connected to servo control wire
Servo Servo1;                    // Servo object to control the garage door motor
String outputCommand = "NaN";    // Stores current command status for display/logging

// MQTT client setup
WiFiClient espClient;                                      // WiFi client for MQTT communication
PubSubClient client(espClient);                           // MQTT client using the WiFi connection

// Declare the callback function prototype before setup()
// This function will be called whenever an MQTT message is received
void callback(char* topic, byte* payload, unsigned int length);


void setup() {
  // Initialize serial communication for debugging
  Serial.begin(9600);
  
  // Setup servo motor configuration for garage door control
  // Allocate hardware timers for PWM servo control
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);
  
  // Set servo frequency to standard 50Hz
  Servo1.setPeriodHertz(50);  // standard 50 hz servo
  
  // Attach servo to the designated GPIO pin
  Servo1.attach(servoPin);
  
  // Wait for serial connection to be established
  while (!Serial) {
    delay(10);
  }
  delay(1000);
  
  // Connect to WiFi network using credentials from sensitiveInformation.h
  WiFi.begin(ssid, password);

  // Wait for WiFi connection to be established
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  
  // Print connection success message and IP address
  Serial.println();
  Serial.print("Connected to the Internet");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  // Configure built-in LED pin as output (for status indication)
  pinMode(LED_BUILTIN, OUTPUT);

  // Configure MQTT broker connection
  client.setServer(mqttServer, mqttPort);           // Set MQTT broker server and port
  client.setCallback(callback);                     // Set the callback function to handle incoming messages

  // Initialize Real Time Clock (RTC) module
  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
    Serial.flush();
  }

  // Set RTC to compile time (uncomment if time needs to be reset)
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  // Start the RTC
  rtc.start();

  // Initialize E-Ink display
  display.begin();
  display.clearBuffer();

    // Establish connection to MQTT Broker
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");

   if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe("Challenges/GarageDoor");      // Subscribe to garage door control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);                                   // Wait 2 seconds before retry
    }
  } 

  // Log system initialization to the shared logging system
  cyberCity.logEvent("System Initialisation...");
}

// MQTT message callback function
// This function is called whenever a message is received on subscribed MQTT topics
// Parameters:
//   topic: The MQTT topic where the message was received
//   payload: The message content as a byte array
//   length: The length of the payload in bytes
void callback(char* topic, byte* payload, unsigned int length) {
  // Print debug information about the received message
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  
  // Print the payload character by character
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();

  // Process garage door commands:
  // Check if the first character of the payload is '0' (close garage door)
  if ((char)payload[0] == '0') {
    Serial.println("shut");
    Servo1.write(0);                    // 0 degrees = close position
    outputCommand = "shut garage door";  // Update status for logging
    delay(1000);                        // Wait for servo to complete movement
  }
  
  // Check if the first character of the payload is '1' (open garage door)
  if ((char)payload[0] == '1') {
    Serial.println("open");
    Servo1.write(90);                   // 90 degrees = open position
    outputCommand = "open garage door"; // Update status for logging
    delay(1000);                        // Wait for servo to complete movement
  }   

}

// Main loop function - runs continuously after setup() completes
void loop() {
   // Check if MQTT client is still connected to the broker
   if (!client.connected()) {
    // If disconnected, attempt to reconnect
    while (!client.connected()) {
      // Try to connect to MQTT broker with client ID "ESP32_Client"
      if (client.connect("ESP32_Client")) {
        // On successful connection, resubscribe to the garage door control topic
        client.subscribe("Challenges/GarageDoor");
      } else {
        // If connection fails, wait 500ms before trying again
        delay(500);
      }
    }
  }
  // Process incoming MQTT messages and maintain connection
  // This must be called regularly to:
  // 1. Check for incoming messages and trigger callback function
  // 2. Send keep-alive packets to maintain connection
  // 3. Handle any queued outgoing messages
  client.loop();
}
