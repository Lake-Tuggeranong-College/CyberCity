// REQUIRED LIBRARIES, DONT REMOVE
#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include "sensitiveInformation.h" //ENSURE WIFI & MQTT IS CONFIGURED CORRECTLY

const int buzzer = 14;

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();

  // Example: turn on/off an LED based on the message received (this is specialised, if you dont need it dont use it.)
  //
  if ((char)payload[0] == '1') {
    Serial.println("LED ON");
    digitalWrite(LED_BUILTIN, HIGH);
    tone(buzzer, 1000);
  } else {
    Serial.println("LED OFF");
    digitalWrite(LED_BUILTIN, LOW);
    tone(buzzer, 0);
  }

  // Example: turn on/off an LED based on ANY message received (this is how this is intended to work, activating when this ESP32's respective
  // challenge is completed)
  //
  // if ((char)payload[0]) {
  //   Serial.println("LED ON");
  //   digitalWrite(redLEDPin, HIGH);
  //   delay(250);
  //   Serial.println("LED OFF");
  //   digitalWrite(redLEDPin, LOW);
  // }
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
      client.subscribe(mqttTopic);  // Subscribe to the control topic
      Serial.println("Connected to topic");
    } else {
      Serial.print("Failed with state ");
      Serial.print(client.state());
      delay(2000);
    }
  }
}

void setup() {
  /*
    STEP 3. CONTINUED.
    DECLARE YOUR pinMode()'s below, e.g:
  
    pinMode(redLEDPin, OUTPUT);
  */
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
  client.subscribe(mqttTopic);
  connectMqtt();
}

void loop() { // The loop function likely does not require change in the majority of circumstances.
  if (!client.connected()) {
    connectMqtt();
  }
  client.loop();  // Check for incoming messages and keep the connection alive
}