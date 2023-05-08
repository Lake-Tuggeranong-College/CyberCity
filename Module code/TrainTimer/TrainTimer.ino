
#include <LiquidCrystal_I2C.h>
LiquidCrystal_I2C lcd(0x3F, 16, 2);
long minute = 3, second = 00;
long countdown_time = (minute * 60) + second;
#include <Wire.h> 
#include "WiFi.h"
#include "sensitiveInformation.h"
#include <CyberCitySharedFuntionality.h>
CyberCitySharedFuntionality cyberCity;
void(* resetFunc) (void) = 0;

void setup() {
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
  }
