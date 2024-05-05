
#define AOUT_PIN A2 // Arduino pin that connects to AOUT pin of moisture sensor

void setup() {
  Serial.begin(9600);
}

void loop() {
  int value = analogRead(AOUT_PIN); // read the analog value from sensor

  Serial.print("Moisture: ");
  Serial.println(value);

  delay(500);
}
