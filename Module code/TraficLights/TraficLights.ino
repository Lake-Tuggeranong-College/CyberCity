#include "sensitiveInformation.h"
void setup() {
  // put your setup code here, to run once:
  pinMode(14,INPUT);
  pinMode(32,OUTPUT);
  pinMode(33,OUTPUT);
  Serial.begin(9600);
  commonSetup();
}

void loop() {
  // put your main code here, to run repeatedly:
  Serial.println(digitalRead(14));
  lights();
}

void lights() {
  if (digitalRead(14)) {
    digitalWrite(32,HIGH);
    digitalWrite(33,LOW);
  }
  else {
    digitalWrite(32,LOW);
    digitalWrite(33,HIGH);
  }
}
