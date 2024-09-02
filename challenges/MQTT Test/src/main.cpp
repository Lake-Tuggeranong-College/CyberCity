#include <WiFi.h>
#include <PubSubClient.h>
#include <Arduino.h>
#include "sensitiveInformation.h"

// MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

// Declare the callback function prototype before setup()
void callback(char* topic, byte* payload, unsigned int length);

void setup() {
  // Initialize Serial for debugging
  Serial.begin(9600);

  pinMode(18, OUTPUT);

  // Connecting to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println();
  Serial.println("Connected to WiFi");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  // Setting up MQTT
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);  // Set the callback function to handle incoming messages

  // Connecting to MQTT Broker
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");

    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe("esp32/control");  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);
    }
  }
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
  if ((char)payload[0] == '1') {
    digitalWrite(18, HIGH);  // Turn the LED on
    Serial.print("LED ON");
  } else {
    digitalWrite(18, LOW);  // Turn the LED off
    Serial.print("LED OFF");
  }
}

void loop() {
  // Keep the MQTT client connected
  if (!client.connected()) {
    while (!client.connected()) {
      Serial.println("Reconnecting to MQTT...");

      if (client.connect("ESP32_Client")) {
        Serial.println("Reconnected to MQTT");
        client.subscribe("esp32/control");
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
