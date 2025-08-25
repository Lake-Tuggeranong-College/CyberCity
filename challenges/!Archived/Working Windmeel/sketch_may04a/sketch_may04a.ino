// Include the Servo library

#include "sensitiveInformation.h"
#include <CyberCitySharedFuntionality.h>
CyberCitySharedFuntionality cyberCity;

#include <Servo.h>
// Declare the Servo pin
int servoPin = 13;
// Create a servo object
Servo Servo1;
void setup() {
  // We need to attach the servo to the used pin number
  Servo1.attach(servoPin);
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
  Servo1.write(0);
  delay(1000);
}
