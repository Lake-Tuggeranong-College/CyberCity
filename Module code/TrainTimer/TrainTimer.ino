#include <LiquidCrystal_I2C.h>
LiquidCrystal_I2C lcd(0x3F, 16, 2);
long minute = 3, second = 00;
long countdown_time = (minute * 60) + second;
#include <Wire.h> 
#include "WiFi.h"
#include "sensitiveInformation.h"
void(* resetFunc) (void) = 0;

void setup() {
 lcd.init();
  lcd.backlight();
  lcd.setCursor(1, 1);
  lcd.print("Arival:");
  lcd.setCursor(0,0);
  lcd.print(" Lego City Train ");
}

void loop() {
 long countdowntime_seconds = countdown_time - (millis() / 1000);
  if (countdowntime_seconds >= 0) {
    long countdown_minute = ((countdowntime_seconds / 60)%60);
    long countdown_sec = countdowntime_seconds % 60;
    lcd.setCursor(9, 1);
    if (countdown_minute < 10) {
      lcd.print("0");
    }
    lcd.print(countdown_minute);
    lcd.print(":");
    if (countdown_sec < 10) {
      lcd.print("0");
    }
    lcd.print(countdown_sec);
  }
  delay(500);
   if (countdown_sec == 0 ) {
      resetFunc()
   }
}
