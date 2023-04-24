# Instructions

This Library - CyberCitySharedFunctionlity - is currently saved in a folder to ease the development process. The library is comprised of two files :

```
CyberCityShareFuntionality.h
CyberCityShareFuntionality.cpp
```

To add this library to your project, zip up the folder, and then choose Sketch -> Include Library - Add Zip Library from within the Arduino IDE.

# File details

The header file (.h) is the file that defines the class to any external files. This file contains the functions that the library is made up of.

The implementation file (.cpp) is where the implementation of each function is stored.

If the code for a function needs to change, you'll probably only need to change the .cpp file. If, however, a new function needs to be added, or the parameters of a function change, then both the .cpp and .h files need to be updated (and match).

## Development

Additional development of this library is encouraged. Make the necessary changes and test them.

To manage versioning, once changes have been made, add an additional comment in ```CyberCitySharedFuntionality.h``` which includes the new version number (make an educated guess as to how further developed it is - no strict rules at this stage), the date that you published the changes, and your initials.

For example:

```// version 1.1.0 24.04.2023 - RC```

## Arduino Sketch

With this library handling the common code, the template of a new Arduino sketch for the project would be:

```
/***************************************************
  Adafruit invests time and resources providing this open source code,
  please support Adafruit and open-source hardware by purchasing
  products from Adafruit!
  Written by Limor Fried/Ladyada for Adafruit Industries.
  MIT license, all text above must be included in any redistribution
 ****************************************************/


#include "sensitiveInformation.h"
#include <CyberCityShareFuntionality.h>
CyberCityShareFuntionality cyberCity;

// Module Specific Code

void setup() {
  /*
  */
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

  // Module Specific Code
}

void loop() {
  
  // Module Specific Code

  cyberCity.updateEPD("Fire Dept", "Temp \tC", sensorData);
  String dataToPost = String(sensorData);
  cyberCity.uploadData(dataToPost, apiKeyValue, sensorName, sensorLocation, 30000, serverName);
  // waits 180 seconds (3 minutes) as per guidelines from adafruit.
  display.clearBuffer();
}

```

```sensitiveInformation.h``` remains the same as done previously.