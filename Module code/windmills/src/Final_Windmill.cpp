#define AIN_PIN A2  // Arduino pin that connects to AOUT pin of moisture sensor
#include <ArduinoJson.h>
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;

#include <ESP32Servo.h>
// Declare the Servo pin
int servoPin = 21;
// Create a servo object
Servo Servo1;
String outputCommand = "NaN";
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

  cyberCity.logEvent("System Initialisation...");
}

void loop() {
  int value = analogRead(AIN_PIN);  // read the analog value from sensor

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
  }
}
 
 
  //Serial.print("Command: ");
 //Serial.print(Command);
 //if (serverCommand == "on0") {
   //outputCommand = "Fan On";
   // Servo1.write(0);
 //} else {
  // /outputCommand = "Fan Off";
   // Servo1.write(90);
 // }
  //display.clearBuffer();

  //delay(500);
//}
