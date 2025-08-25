/*
 * ESP32 Alarm System with Musical Buzzer
 * This code creates an alarm system that plays "A Cruel Angel's Thesis" 
 * from Neon Genesis Evangelion when triggered via MQTT commands
 * Features:
 * - MQTT-controlled alarm activation
 * - Musical melody playback using buzzer
 * - Built-in LED status indication
 * - WiFi connectivity for remote control
 */

// REQUIRED LIBRARIES, DONT REMOVE
#include <Arduino.h>                          // Core Arduino functions
#include <WiFi.h>                             // WiFi connectivity library
#include <PubSubClient.h>                     // MQTT client library
#include "sensitiveInformation.h"             // WiFi & MQTT credentials - ENSURE CONFIGURED CORRECTLY
#include "pitches.h"                          // Musical note frequency definitions

// Hardware pin configuration
const int buzzer = 14;                        // GPIO pin connected to piezo buzzer

// Musical melody data for "A Cruel Angel's Thesis" from Neon Genesis Evangelion
// Array of musical note frequencies (defined in pitches.h)
int melody[] = {
  NOTE_C5, NOTE_DS5, NOTE_F5, NOTE_DS5, NOTE_F5, NOTE_F5, NOTE_F5,
  NOTE_AS5, NOTE_GS5, NOTE_G5, NOTE_F5, NOTE_G5, REST, NOTE_G5, NOTE_AS5,
  NOTE_C6, NOTE_F5, NOTE_DS5, NOTE_AS5, NOTE_AS5, NOTE_G5, NOTE_AS5,
  NOTE_AS5, NOTE_C6, 
};

// Note durations: 4 = quarter note, 8 = eighth note, 2 = half note, etc.
// Lower number = longer duration
int noteDurations[] = {
  2, 2, 2, 2, 4, 4, 4,
  4, 4, 8, 4, 2, 4, 2, 2,
  2, 2, 4, 4, 4, 4, 4,
  2, 2,
};

// Function to play the complete melody
// Iterates through melody array and plays each note with appropriate timing
void playEvangelionTheme() {
  // Loop through each note in the melody
  for (int i = 0; i < sizeof(melody) / sizeof(melody[0]); i++) {
    // Calculate note duration: 1000ms divided by note duration value
    // (smaller duration number = longer note)
    int duration = 1000 / noteDurations[i];
    
    // Play the current note on the buzzer for the calculated duration
    tone(buzzer, melody[i], duration);
    
    // Pause between notes (30% longer than note duration for clarity)
    delay(duration * 1.3);
    
    // Stop the tone to create clear separation between notes
    noTone(buzzer);
  }
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

  // Process alarm commands:
  // Check if the first character is '1' (activate alarm)
  if ((char)payload[0] == '1') {
    Serial.println("Playing EvangelionTheme");
    playEvangelionTheme();                    // Play the complete melody
  } else {
    // Any other command (including '0') deactivates the alarm
    Serial.println("LED OFF");
    digitalWrite(LED_BUILTIN, LOW);           // Turn off status LED
    tone(buzzer, 0);                          // Silence the buzzer
  }
}

// MQTT client configuration and connection management
// Declare the callback function prototype before setup()
void callback(char* topic, byte* payload, unsigned int length);

// WiFi and MQTT client setup
WiFiClient espClient;                         // WiFi client for MQTT communication
PubSubClient client(espClient);               // MQTT client using the WiFi connection

// Function to establish connection to MQTT broker
void connectMqtt() {
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    
    // Attempt to connect using client ID from sensitiveInformation.h
    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe(mqttTopic);            // Subscribe to alarm control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);                            // Wait 2 seconds before retry
    }
  }
}

// System initialization function - runs once when ESP32 starts
void setup() {
  // Configure hardware pins
  pinMode(LED_BUILTIN, OUTPUT);               // Built-in LED for status indication
  pinMode(buzzer, OUTPUT);                    // Buzzer pin for audio output

  // Initialize serial communication for debugging
  Serial.begin(9600);
  while (!Serial) {
    delay(10);                                // Wait for serial connection
  }
  delay(1000);

  // Connect to WiFi network using credentials from sensitiveInformation.h
  WiFi.begin(ssid, password);

  // Wait for WiFi connection to be established
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  
  // Print connection success information
  Serial.println();
  Serial.print("Connected to WiFI");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  // Configure MQTT broker connection
  client.setServer(mqttServer, mqttPort);     // Set MQTT broker address and port
  client.setCallback(callback);               // Set message handler function
  client.subscribe(mqttTopic);                // Subscribe to alarm control topic
  connectMqtt();                              // Establish initial MQTT connection
}

// Main program loop - runs continuously after setup() completes
void loop() { 
  // Check if MQTT connection is still active
  if (!client.connected()) {
    connectMqtt();                            // Reconnect if connection lost
  }
  
  // Process incoming MQTT messages and maintain connection
  // This must be called regularly to:
  // 1. Check for incoming messages and trigger callback function
  // 2. Send keep-alive packets to maintain connection
  // 3. Handle any queued outgoing messages
  client.loop();
}