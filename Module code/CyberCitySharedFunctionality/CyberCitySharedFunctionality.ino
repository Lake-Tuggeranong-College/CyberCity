//version 1.0.0 28.03.2023
void commonSetup() {
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
  if (! rtc.begin()) {
    Serial.println("Couldn't find RTC");
    Serial.flush();
    //    abort();
  }

  // The following line can be uncommented if the time needs to be reset.
  rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));

  rtc.start();

  //EINK
  display.begin();
  display.clearBuffer();


  logEvent("System Initialisation...");

}
//   V ?  should it go to fd code? 
void updateEPD(String title, String dataTitle, float dataToDisplay) {

  // Indigenous Country Name
  drawText(title, EPD_BLACK, 2, 0, 0);

  // Config
  drawText(WiFi.localIP().toString(), EPD_BLACK, 1, 130, 80);
  drawText(getTimeAsString(), EPD_BLACK, 1, 130, 100);
  drawText(getDateAsString(), EPD_BLACK, 1, 130, 110);

  // Draw lines to divvy up the EPD
  display.drawLine(0, 20, 250, 20, EPD_BLACK);
  display.drawLine(125, 20, 125, 122, EPD_BLACK);
  display.drawLine(0, 75, 250, 75, EPD_BLACK);

  drawText(dataTitle, EPD_BLACK, 2, 0, 80);
  drawText(String(dataToDisplay), EPD_BLACK, 4, 0, 95);

  logEvent("Updating the EPD");
  display.display();
    
}

void drawText(String text, uint16_t color, int textSize, int x, int y) {
  display.setCursor(x, y);
  display.setTextColor(color);
  display.setTextSize(textSize);
  display.setTextWrap(true);
  display.print(text);
}
//    I
String getDateAsString() {
  DateTime now = rtc.now();

  // Converts the date into a human-readable format.
  char humanReadableDate[20];
  sprintf(humanReadableDate, "%02d/%02d/%02d", now.day(), now.month(), now.year());

  return humanReadableDate;
}

String getTimeAsString() {
  DateTime now = rtc.now();

  // Converts the time into a human-readable format.
  char humanReadableTime[20];
  sprintf(humanReadableTime, "%02d:%02d:%02d", now.hour(), now.minute(), now.second());

  return humanReadableTime;
}

void logEvent(String dataToLog) {
  /*
     Log entries to a file stored in SPIFFS partition on the ESP32.
  */
  // Get the updated/current time
  DateTime rightNow = rtc.now();
  char csvReadableDate[25];
  sprintf(csvReadableDate, "%02d,%02d,%02d,%02d,%02d,%02d,",  rightNow.year(), rightNow.month(), rightNow.day(), rightNow.hour(), rightNow.minute(), rightNow.second());

  String logTemp = csvReadableDate + dataToLog + "\n"; // Add the data to log onto the end of the date/time

  const char * logEntry = logTemp.c_str(); //convert the logtemp to a char * variable

  //Add the log entry to the end of logevents.csv

  // Output the logEvents - FOR DEBUG ONLY. Comment out to avoid spamming the serial monitor.
  //  readFile(SPIFFS, "/logEvents.csv");

  Serial.print("\nEvent Logged: ");
  Serial.println(logEntry);
}

void uploadData (String dataToPost, int delayBetweenPosts) {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    // Your Domain name with URL path or IP address with path
    http.begin(client, serverName);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Prepare your HTTP POST request data
    String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorName + "&location=" + sensorLocation + "&sensorValue=" + dataToPost;
    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);

    // You can comment the httpRequestData variable above
    // then, use the httpRequestData variable below (for testing purposes without the BME280 sensor)
    // String httpRequestData = "api_key=tPmAT5Ab3j7F9&sensor=BME280&location=Office&value1=24.75&value2=49.54&value3=1005.14";

    // Send HTTP POST request
    int httpResponseCode = http.POST(httpRequestData);

    // If you need an HTTP request with a content type: text/plain
    //http.addHeader("Content-Type", "text/plain");
    // int httpResponseCode = http.POST("Hello, World!");

    // If you need an HTTP request with a content type: application/json, use the following:
    // http.addHeader("Content-Type", "application/json");
    // int httpResponseCode = http.POST("{\"value1\":\"19\",\"value2\":\"67\",\"value3\":\"78\"}");


    if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    }
    else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    // Free resources
    http.end();
  }
  else {
    Serial.println("WiFi Disconnected");
  }
  //Send an HTTP POST request every 30 seconds
  delay(delayBetweenPosts);

}
