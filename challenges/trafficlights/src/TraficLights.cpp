/*
 * ESP32 Traffic Light Control System
 * This code controls two sets of traffic lights via MQTT commands
 * Features:
 * - Normal traffic light sequence (green -> yellow -> red cycles)
 * - Chaos mode (all lights flashing rapidly)
 * - Ultrasonic sensor integration for vehicle detection
 * - MQTT communication for remote control
 */

// Include necessary libraries
#include <CyberCitySharedFunctionality.h>     // Custom library for shared project functionality
#include <Arduino.h>                          // Core Arduino functions
#include <WiFi.h>                             // WiFi connectivity library
#include <PubSubClient.h>                     // MQTT client library
#include "sensitiveInformation.h"             // Contains WiFi credentials and MQTT settings

// === Pin Definitions ===
// Ultrasonic sensor pins for vehicle detection
#define TRIG_PIN 13    // Trigger pin for ultrasonic sensor
#define ECHO_PIN 12    // Echo pin for ultrasonic sensor

// Traffic Light 1 GPIO pin assignments
const int TL1_GREEN  = 15;   // Green LED for traffic light 1
const int TL1_RED    = 16;   // Red LED for traffic light 1
const int TL1_YELLOW = 17;   // Yellow LED for traffic light 1

// Traffic Light 2 GPIO pin assignments
const int TL2_GREEN  = 23;   // Green LED for traffic light 2
const int TL2_RED    = 14;   // Red LED for traffic light 2
const int TL2_YELLOW = 22;   // Yellow LED for traffic light 2

// === Objects ===
CyberCitySharedFunctionality cyberCity;       // Instance of shared functionality class
WiFiClient espClient;                          // WiFi client for MQTT communication
PubSubClient client(espClient);                // MQTT client using the WiFi connection

// === Mode Tracking ===
// Enumeration to define the different operating modes
enum Mode { MODE_NORMAL, MODE_CHAOS };
Mode currentMode = MODE_NORMAL;                // Start in normal traffic light mode

// === Chaos Mode Timing ===
unsigned long lastBlinkTime = 0;               // Timestamp for last blink in chaos mode
bool lightsOn = false;                         // Current state of lights in chaos mode (on/off)

// === Helper to set lights ===
// Function to control all traffic light LEDs simultaneously
// Parameters: g1, y1, r1 = Traffic Light 1 (Green, Yellow, Red)
//            g2, y2, r2 = Traffic Light 2 (Green, Yellow, Red)
// Values: HIGH = LED on, LOW = LED off
void setLights(int g1, int y1, int r1, int g2, int y2, int r2) {
  digitalWrite(TL1_GREEN,  g1);    // Set Traffic Light 1 Green
  digitalWrite(TL1_YELLOW, y1);    // Set Traffic Light 1 Yellow
  digitalWrite(TL1_RED,    r1);    // Set Traffic Light 1 Red
  digitalWrite(TL2_GREEN,  g2);    // Set Traffic Light 2 Green
  digitalWrite(TL2_YELLOW, y2);    // Set Traffic Light 2 Yellow
  digitalWrite(TL2_RED,    r2);    // Set Traffic Light 2 Red
}

// === Normal Mode Sequence ===
// Function to run one complete normal traffic light cycle
// Traffic Light 1: Green -> Yellow -> Red
// Traffic Light 2: Red -> Red -> Green -> Yellow -> Red (opposite timing)
void lightsNormal() {
  // Phase 1: TL1 Green, TL2 Red (5 seconds)
  setLights(HIGH, LOW, LOW, LOW, LOW, HIGH);  delay(5000);
  
  // Phase 2: TL1 Yellow, TL2 Red (1 second warning)
  setLights(LOW, HIGH, LOW, LOW, LOW, HIGH);  delay(1000);
  
  // Phase 3: TL1 Red, TL2 Green (5 seconds)
  setLights(LOW, LOW, HIGH, HIGH, LOW, LOW);  delay(5000);
  
  // Phase 4: TL1 Red, TL2 Yellow (1 second warning)
  setLights(LOW, LOW, HIGH, LOW, HIGH, LOW);  delay(1000);
  
  // Phase 5: Both Red (0.5 second safety gap)
  setLights(LOW, LOW, HIGH, LOW, LOW, HIGH);  delay(500);
}

// === Continuous Chaos Mode Flash ===
// Function to create a chaotic flashing pattern for all traffic lights
// All lights flash on and off every 500ms to simulate system malfunction
void runChaosMode() {
  unsigned long now = millis();                // Get current time in milliseconds
  
  // Check if 500ms have passed since last blink
  if (now - lastBlinkTime >= 500) {           // Toggle every 500ms
    lastBlinkTime = now;                      // Update last blink timestamp
    lightsOn = !lightsOn;                     // Toggle light state
    
    if (lightsOn) {
      // Turn all lights on (chaos!)
      setLights(HIGH, HIGH, HIGH, HIGH, HIGH, HIGH);
    } else {
      // Turn all lights off
      setLights(LOW, LOW, LOW, LOW, LOW, LOW);
    }
  }
}

// === Sonar Sensor (basic trigger pulse) ===
// Function to trigger the ultrasonic sensor for vehicle detection
// Sends a 10-microsecond pulse to measure distance to objects
void sonarSensorData() {
  digitalWrite(TRIG_PIN, LOW);       // Ensure trigger pin is low
  delayMicroseconds(2);              // Wait 2 microseconds
  digitalWrite(TRIG_PIN, HIGH);      // Send trigger pulse
  delayMicroseconds(10);             // Hold pulse for 10 microseconds
  digitalWrite(TRIG_PIN, LOW);       // End trigger pulse
  // Optional: measure pulseIn(ECHO_PIN, HIGH) for distance calculation
  // Distance = (pulse_duration * 0.034) / 2 (in centimeters)
}

// === MQTT Connection ===
// Function to establish and maintain connection to MQTT broker
void mqttConnect() {
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    
    // Attempt to connect using the client ID from sensitiveInformation.h
    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      // Subscribe to traffic light control topic
      client.subscribe("Challenges/TrafficLights");
    } else {
      Serial.print("MQTT connection failed, state: ");
      Serial.println(client.state());
      delay(1000);                   // Wait 1 second before retry
    }
  }
}

// Function to maintain MQTT connection and process messages
void mqttLoop() {
  // Check if still connected, reconnect if necessary
  if (!client.connected()) {
    mqttConnect();
  }
  // Process incoming MQTT messages and maintain connection
  client.loop();
}

// === MQTT Callback ===
// Function called when MQTT message is received on subscribed topics
// Parameters:
//   topic: The MQTT topic where message was received
//   payload: Message content as byte array
//   length: Length of the payload
void callback(char* topic, byte* payload, unsigned int length) {
  // Convert payload bytes to string
  String message;
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  // Print received message for debugging
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  Serial.println(message);

  // Process commands:
  // "0" = Enable chaos mode (all lights flashing)
  if (message == "0") {
    Serial.println("CHAOS MODE!");
    currentMode = MODE_CHAOS;
  } 
  // "1" = Enable normal mode (standard traffic light sequence)
  else if (message == "1") {
    Serial.println("NORMAL MODE!");
    currentMode = MODE_NORMAL;
    lightsNormal();                  // Run one complete normal cycle immediately
  }
}

// === Setup ===
// Initialization function - runs once when ESP32 starts
void setup() {
  // Initialize serial communication for debugging
  Serial.begin(9600);

  // Configure ultrasonic sensor pins
  pinMode(TRIG_PIN, OUTPUT);         // Trigger pin sends ultrasonic pulse
  pinMode(ECHO_PIN, INPUT);          // Echo pin receives reflected pulse

  // Configure all traffic light LED pins as outputs
  pinMode(TL1_GREEN, OUTPUT);        // Traffic Light 1 Green LED
  pinMode(TL1_RED, OUTPUT);          // Traffic Light 1 Red LED
  pinMode(TL1_YELLOW, OUTPUT);       // Traffic Light 1 Yellow LED
  pinMode(TL2_GREEN, OUTPUT);        // Traffic Light 2 Green LED
  pinMode(TL2_RED, OUTPUT);          // Traffic Light 2 Red LED
  pinMode(TL2_YELLOW, OUTPUT);       // Traffic Light 2 Yellow LED

  // Connect to WiFi network using credentials from sensitiveInformation.h
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.print("Connected! IP: ");
  Serial.println(WiFi.localIP());

  // Initialize Real Time Clock (RTC) module
  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
  }
  // Set RTC to compile time (adjust as needed)
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  rtc.start();

  // Initialize E-Ink display
  display.begin();
  display.clearBuffer();

  // Log system initialization to shared logging system
  cyberCity.logEvent("System Initialisation...");

  // Configure MQTT settings and establish connection
  client.setServer(mqttServer, mqttPort);    // Set MQTT broker address and port
  client.setCallback(callback);              // Set message handler function
  mqttConnect();                             // Connect to MQTT broker
}

// === Loop ===
// Main program loop - runs continuously after setup() completes
void loop() {
  // Trigger ultrasonic sensor for vehicle detection
  sonarSensorData();
  
  // Maintain MQTT connection and process incoming messages
  mqttLoop();

  // Execute current operating mode
  if (currentMode == MODE_CHAOS) {
    // Run chaos mode: all lights flashing rapidly
    runChaosMode();
  }
  // Note: Normal mode runs when triggered by MQTT command "1"
  // and doesn't need continuous execution in the loop
}
