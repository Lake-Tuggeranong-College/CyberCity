#include <PowerFunctions.h>   //Power Functions Library
// EINK
#include "Adafruit_ThinkInk.h"
#include <CyberCitySharedFunctionality.h>     // Custom library for shared project functionality
#include <Arduino.h>                          // Core Arduino functions
#include <WiFi.h>                             // WiFi connectivity library
#include <PubSubClient.h>                     // MQTT client library
#include "sensitiveInformation.h"             // Contains WiFi credentials and MQTT settings


#define EPD_CS      15
#define EPD_DC      33
#define SRAM_CS     32
#define EPD_RESET   -1 // can set to -1 and share with microcontroller Reset!
#define EPD_BUSY    -1 // can set to -1 to not use a pin (will wait a fixed delay)

//IR Channels
#define CH1 0x0
#define CH2 0x1
#define CH3 0x2
#define CH4 0x3

//IR Transmission
#define IR_TRANS_IN   21  //IR Trans PIN
#define IR_DEBUG_OFF  0  //IR Debug Mode Off
#define IR_DEBUG_ON   1  //IR Debug Mode On

//Call PowerFunctions parameters
PowerFunctions pf(IR_TRANS_IN, CH1, IR_DEBUG_ON);
CyberCitySharedFunctionality cyberCity;       // Instance of shared functionality class
WiFiClient espClient;                          // WiFi client for MQTT communication
PubSubClient client(espClient);   

// Current time
unsigned long currentTime = millis();
// Previous time
unsigned long previousTime = 0;
// Define timeout time in milliseconds (example: 2000ms = 2s)
const long timeoutTime = 2000;

// Single and dual motor control is defined
void step(uint8_t output, uint8_t pwm, uint16_t time) {
  pf.combo_pwm(output, pwm);
  pf.single_pwm(output, pwm);
}
// Single increment for speed is defined
void increment(uint8_t output) {
  pf.single_increment(output);
}
// Single decrement for speed is defined
void decrement(uint8_t output) {
  pf.single_decrement(output);
}

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
    Serial.println("HALT");
    step(RED, PWM_BRK, 0);
  } 
  // "1" = Enable normal mode (standard traffic light sequence)
  else if (message == "1") {
    Serial.println("ADVANCE");
    step(RED, PWM_FWD3, 0);
  }
}

void setup() {
  // Connect to Wi-Fi network with SSID and password.
  Serial.begin(9600);
  while (!Serial) {
    delay(10);
  }
  delay(1000);
  pinMode(21, OUTPUT);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.print("Connected! IP: ");
  Serial.println(WiFi.localIP());  

  client.setServer(mqttServer, mqttPort);    // Set MQTT broker address and port
  client.setCallback(callback);              // Set message handler function
  mqttConnect();                             // Connect to MQTT broker
}

void loop() {
  mqttLoop();
}