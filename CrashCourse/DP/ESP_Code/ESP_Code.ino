#include "config.h"

#include <Wire.h>
#include "Adafruit_ADT7410.h"
Adafruit_ADT7410 tempsensor = Adafruit_ADT7410();

void setup() {
  Serial.begin(9600);
  if (!tempsensor.begin()) {
    Serial.println("Couldn't find ADT7410!");
    logEvent("Couldn't find ADT7410!");
    while (1);
  }
}

void loop() {
  // put your main code here, to run repeatedly:

}
