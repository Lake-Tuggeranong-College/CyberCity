#include <Wire.h> 
#include <LiquidCrystal_I2C.h>

LiquidCrystal_I2C lcd(0x3F, 16, 2);

void setup()
{
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("  I2C LCD with ");
  lcd.setCursor(0,1);
  lcd.print("  ESP32 DevKit ");
  //delay(2000);
}


void loop()
{
  
}
