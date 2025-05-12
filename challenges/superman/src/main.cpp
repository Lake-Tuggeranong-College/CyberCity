// REQUIRED LIBRARIES, DONT REMOVE
#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include "sensitiveInformation.h" //ENSURE WIFI & MQTT IS CONFIGURED CORRECTLY
#include "pitches.h"

const int buzzer = 14;
// Notes for "Hot Milk"
int melody[] = {
  NOTE_G6, NOTE_FS6, NOTE_D6, NOTE_B5, NOTE_A5, REST, NOTE_G5, NOTE_A5,
  NOTE_G6, NOTE_FS6, NOTE_A5, NOTE_D6, NOTE_DS6, NOTE_D6, NOTE_A5
};

// Durations: 4 = quarter note, 8 = eighth note, etc.
int noteDurations[] = {
  3, 3, 3, 3, 
  3, 1, 3, 5,
  3, 2, 8, 3,
  8, 5, 6
};

void playEvangelionTheme() {
  for (int i = 0; i < sizeof(melody) / sizeof(melody[0]); i++) {
    int duration = 1000 / noteDurations[i];
    tone(buzzer, melody[i], duration * 0.9);
    delay(duration * 1.3); // pause between notes
    noTone(buzzer);
  }
  delay(1500);
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
    Serial.println("Playing EvangelionTheme");
    playEvangelionTheme();
  } else {
    Serial.println("LED OFF");
    digitalWrite(LED_BUILTIN, LOW);
    tone(buzzer, 0);
  }
}





// Declare the callback function prototype before setup()
void callback(char* topic, byte* payload, unsigned int length);

// MQTT client setup
WiFiClient espClient;
PubSubClient client(espClient);

void connectMqtt() {
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    if (client.connect(mqttClient)) {
      Serial.println("Connected to MQTT");
      client.subscribe("Challenges/module6");  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);
    }
  }
}

void setup() {
  pinMode(LED_BUILTIN, OUTPUT); // Built in LED
  pinMode(buzzer, OUTPUT); // Set buzzer - pin 9 as an output

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
  Serial.print("Connected to WiFI");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  // Setting up MQTT
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);  // Set the callback function to handle incoming messages
  client.subscribe("Challenges/module6");
  connectMqtt();
}

void loop() { // The loop function likely does not require change in the majority of circumstances.
  if (!client.connected()) {
    connectMqtt();
  }
  client.loop();  // Check for incoming messages and keep the connection alive
}