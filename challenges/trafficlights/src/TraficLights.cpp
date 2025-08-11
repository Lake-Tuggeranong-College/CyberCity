#include <CyberCitySharedFunctionality.h>
#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include "sensitiveInformation.h" // Your existing file with ssid, password, mqttServer, mqttPort, mqttClient

// === Pin Definitions ===
#define TRIG_PIN 13
#define ECHO_PIN 12

// Traffic Light 1
const int TL1_GREEN  = 15;
const int TL1_RED    = 16;
const int TL1_YELLOW = 17;

// Traffic Light 2
const int TL2_GREEN  = 23;
const int TL2_RED    = 14;
const int TL2_YELLOW = 22;

// === Objects ===
CyberCitySharedFunctionality cyberCity;
WiFiClient espClient;
PubSubClient client(espClient);

// === Mode Tracking ===
enum Mode { MODE_NORMAL, MODE_CHAOS };
Mode currentMode = MODE_NORMAL;

// === Chaos Mode Timing ===
unsigned long lastBlinkTime = 0;
bool lightsOn = false;

// === Helper to set lights ===
void setLights(int g1, int y1, int r1, int g2, int y2, int r2) {
  digitalWrite(TL1_GREEN,  g1);
  digitalWrite(TL1_YELLOW, y1);
  digitalWrite(TL1_RED,    r1);
  digitalWrite(TL2_GREEN,  g2);
  digitalWrite(TL2_YELLOW, y2);
  digitalWrite(TL2_RED,    r2);
}

// === Normal Mode Sequence ===
void lightsNormal() {
  setLights(HIGH, LOW, LOW, LOW, LOW, HIGH);  delay(5000);
  setLights(LOW, HIGH, LOW, LOW, LOW, HIGH);  delay(1000);
  setLights(LOW, LOW, HIGH, HIGH, LOW, LOW);  delay(5000);
  setLights(LOW, LOW, HIGH, LOW, HIGH, LOW);  delay(1000);
  setLights(LOW, LOW, HIGH, LOW, LOW, HIGH);  delay(500);
}

// === Continuous Chaos Mode Flash ===
void runChaosMode() {
  unsigned long now = millis();
  if (now - lastBlinkTime >= 500) { // Toggle every 500ms
    lastBlinkTime = now;
    lightsOn = !lightsOn;
    if (lightsOn) {
      setLights(HIGH, HIGH, HIGH, HIGH, HIGH, HIGH);
    } else {
      setLights(LOW, LOW, LOW, LOW, LOW, LOW);
    }
  }
}

// === Sonar Sensor (basic trigger pulse) ===
void sonarSensorData() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  // Optional: measure pulseIn(ECHO_PIN, HIGH) for distance
}

// === MQTT Connection ===
void mqttConnect() {
  while (!client.connected()) {
    Serial.println("Connecting to MQTT...");
    if (client.connect(mqttClient)) { // Uses your original mqttClient variable
      Serial.println("Connected to MQTT");
      client.subscribe("Challenges/TrafficLights");
    } else {
      Serial.print("MQTT connection failed, state: ");
      Serial.println(client.state());
      delay(1000);
    }
  }
}

void mqttLoop() {
  if (!client.connected()) {
    mqttConnect();
  }
  client.loop();
}

// === MQTT Callback ===
void callback(char* topic, byte* payload, unsigned int length) {
  String message;
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  Serial.println(message);

  if (message == "0") {
    Serial.println("CHAOS MODE!");
    currentMode = MODE_CHAOS;
  } else if (message == "1") {
    Serial.println("NORMAL MODE!");
    currentMode = MODE_NORMAL;
    lightsNormal();
  }
}

// === Setup ===
void setup() {
  Serial.begin(9600);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  pinMode(TL1_GREEN, OUTPUT);
  pinMode(TL1_RED, OUTPUT);
  pinMode(TL1_YELLOW, OUTPUT);
  pinMode(TL2_GREEN, OUTPUT);
  pinMode(TL2_RED, OUTPUT);
  pinMode(TL2_YELLOW, OUTPUT);

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.print("Connected! IP: ");
  Serial.println(WiFi.localIP());

  // RTC setup
  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
  }
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  rtc.start();

  // Display setup
  display.begin();
  display.clearBuffer();

  cyberCity.logEvent("System Initialisation...");

  // MQTT setup
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);
  mqttConnect();
}

// === Loop ===
void loop() {
  sonarSensorData();
  mqttLoop();

  if (currentMode == MODE_CHAOS) {
    runChaosMode();
  }
}
