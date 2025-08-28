
int startingTimer = 180;

int countdownSeconds = startingTimer;
#include <LiquidCrystal_I2C.h>

#include <ArduinoJson.h>

LiquidCrystal_I2C lcd(0x3F, 16, 2);
#include <Wire.h>
#include "WiFi.h"
#include "sensitiveInformation.h"
#include <CyberCitySharedFunctionality.h>
CyberCitySharedFunctionality cyberCity;

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
  lcd.setCursor(0, 0);
  lcd.print(" Lego City Train ");
}

void loop() {

  //  int minutesLeft = countdownSeconds % 60;
  //  int secondsLeft = countdownSeconds / 60;

  countdownSeconds = countdownSeconds - 1;
  delay(1000);

  //  long countdownSeconds = countdownSeconds - (millis() / 1000);
  if (countdownSeconds >= 0) {
    long countdown_minute = ((countdownSeconds / 60) % 60);
    long countdown_seconds = countdownSeconds % 60;
    lcd.setCursor(9, 1);
    if (countdown_minute < 10) {
      lcd.print("0");
    }
    lcd.print(countdown_minute);
    lcd.print(":");
    if (countdown_seconds < 10) {
      lcd.print("0");
    }
    lcd.print(countdown_seconds);
  } else {
    countdownSeconds = startingTimer;
  }


  //float sensorData = (float)countdownSeconds;
  String dataToPost = String(countdownSeconds);
  //cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 250, serverName);
  String payload = cyberCity.dataTransfer(dataToPost, apiKeyValue, sensorName, sensorLocation, 250, serverName, true, true);
  Serial.print("Payload from server:");
  Serial.println(payload);
  DynamicJsonDocument doc(1024);
  //  Serial.println(deserializeJson(doc, payload));
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    Serial.print(F("deserializeJson() failed: "));
    Serial.println(error.f_str());
    return;
  }
  const char* command = doc["command"];
  Serial.print("Command: ");
  Serial.print(command);
  delay(500);
}
