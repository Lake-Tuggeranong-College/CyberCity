#define AOUT_PIN A2  // Arduino pin that connects to AOUT pin of moisture sensor

#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;

#include <ESP32Servo.h>
// Declare the Servo pin
int servoPin = 13;
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
  Servo1.attach(servoPin, 1000, 2000);
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
  int value = analogRead(AOUT_PIN);  // read the analog value from sensor

  Serial.println(value);
  float sensorData = value * 1.0;
  cyberCity.updateEPD("Farm", "value", sensorData, outputCommand);
  String dataToPost = String(sensorData);
  // cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 40000, serverName, true, true);
  //notes need to // the next to line 
  //int payloadLocation = payload.indexOf("Payload:");
// char serverCommand = payload.charAt(payloadLocation + 8);
 
  Serial.print("Command: ");
  Serial.print(serverCommand);
  if (serverCommand == '1') {
    outputCommand = "Fan On";
    Servo1.write(0);
  } else {
    outputCommand = "Fan Off";
    Servo1.write(90);
  }
  display.clearBuffer();

  delay(500);
}
