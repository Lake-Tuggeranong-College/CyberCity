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

// 2.13" Monochrome displays with 250x122 pixels and SSD1675 chipset
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);

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

void setup() {
  // Connect to Wi-Fi network with SSID and password.
  Serial.begin(9600);
  while (!Serial) {
    delay(10);
  }
  delay(1000);
  pinMode(21, OUTPUT);
}

void loop() {
  step(RED, PWM_FWD3, 0); // The step function is run and speed is set to 7
  delay(2000);
  step(RED, PWM_BRK, 0);
  delay(2000);
  step(RED, PWM_REV1, 0); // The step function is run and speed is set to 1
  delay(2000);

}