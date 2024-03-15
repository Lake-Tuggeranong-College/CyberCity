// include the library code:
#include <Arduino.h>
#include <Wire.h> 
#include <LiquidCrystal_I2C.h>

// set the LCD number of columns and rows
int lcdColumns = 16;
int lcdRows = 2;

// set LCD address, number of columns and rows
// if you don't know your display address, run an I2C scanner sketch
LiquidCrystal_I2C lcd(0x27, lcdColumns, lcdRows);  

// ESP32 Blink test.
int led = LED_BUILTIN;

void setup() {
  // Some boards work best if we also make a serial connection
  Serial.begin(9600);

  // set LED to be an output pin
  pinMode(led, OUTPUT);

  // initialize the lcd 
  lcd.init();
  // turn on LCD backlight     
  lcd.backlight();

// Process to detect I2C device address.

  // Wire.begin();
  // Serial.println("\nI2C Scanner");
}

void loop() {
  digitalWrite(led, HIGH);   // turn the LED on (HIGH is the voltage level)
  Serial.println("LED is current on!");   // Output text on serial monitor to show it's on.
  delay(500);                // wait for a half second

  digitalWrite(led, LOW);    // turn the LED off by making the voltage LOW
  Serial.println("LED is current off!");  // Output text on serial monitor to show it's off.
  delay(500);                // wait for a half second

  // set cursor to first column, first row
  lcd.setCursor(0, 0);
  // print message
  lcd.print("Hello, World!");
  delay(1000);
  // clears the display to print new message
  lcd.clear();
  // set cursor to first column, second row
  lcd.setCursor(0,1);
  lcd.print("Hello, World!");
  delay(1000);
  lcd.clear(); 

// Code to find I2C device address.

  /* 
  byte error, address;
  int nDevices;
  Serial.println("Scanning...");
  nDevices = 0;
  for(address = 1; address < 127; address++ ) {
    Wire.beginTransmission(address);
    error = Wire.endTransmission();
    if (error == 0) {
      Serial.print("I2C device found at address 0x");
      if (address<16) {
        Serial.print("0");
      }
      Serial.println(address,HEX);
      nDevices++;
    }
    else if (error==4) {
      Serial.print("Unknow error at address 0x");
      if (address<16) {
        Serial.print("0");
      }
      Serial.println(address,HEX);
    }    
  }
  if (nDevices == 0) {
    Serial.println("No I2C devices found\n");
  }
  else {
    Serial.println("done\n");
  }
  delay(5000); 
  */
}