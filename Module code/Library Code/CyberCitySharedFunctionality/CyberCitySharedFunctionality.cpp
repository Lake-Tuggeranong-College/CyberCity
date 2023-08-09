#include "CyberCitySharedFunctionality.h"
ThinkInk_213_Mono_B72 display(EPD_DC, EPD_RESET, EPD_CS, SRAM_CS, EPD_BUSY);
RTC_PCF8523 rtc;

CyberCitySharedFunctionality::CyberCitySharedFunctionality()
{
}

void CyberCitySharedFunctionality::commonSetup()
{
}

void CyberCitySharedFunctionality::updateEPD(String title, String dataTitle, float dataToDisplay, String outputCommand)
{

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
  drawText(outputCommand, EPD_BLACK, 3, 0, 40);

  logEvent("Updating the EPD");
  display.display();
}

void CyberCitySharedFunctionality::drawText(String text, uint16_t color, int textSize, int x, int y)
{
  display.setCursor(x, y);
  display.setTextColor(color);
  display.setTextSize(textSize);
  display.setTextWrap(true);
  display.print(text);
}

String CyberCitySharedFunctionality::getDateAsString()
{
  DateTime now = rtc.now();

  // Converts the date into a human-readable format.
  char humanReadableDate[20];
  sprintf(humanReadableDate, "%02d/%02d/%02d", now.day(), now.month(), now.year());

  return humanReadableDate;
}

String CyberCitySharedFunctionality::getTimeAsString()
{
  DateTime now = rtc.now();

  // Converts the time into a human-readable format.
  char humanReadableTime[20];
  sprintf(humanReadableTime, "%02d:%02d:%02d", now.hour(), now.minute(), now.second());
									
  return humanReadableTime;
}

void CyberCitySharedFunctionality::logEvent(String dataToLog)
{
  /*
     Log entries to a file stored in SPIFFS partition on the ESP32.
  */
  // Get the updated/current time
  DateTime rightNow = rtc.now();
  char csvReadableDate[25];
  sprintf(csvReadableDate, "%02d,%02d,%02d,%02d,%02d,%02d,", rightNow.year(), rightNow.month(), rightNow.day(), rightNow.hour(), rightNow.minute(), rightNow.second());

  String logTemp = csvReadableDate + dataToLog + "\n"; // Add the data to log onto the end of the date/time

  const char *logEntry = logTemp.c_str(); // convert the logtemp to a char * variable

  // Add the log entry to the end of logevents.csv

  // Output the logEvents - FOR DEBUG ONLY. Comment out to avoid spamming the serial monitor.
  //  readFile(SPIFFS, "/logEvents.csv");

  Serial.print("\nEvent Logged: ");
  Serial.println(logEntry);
}

String CyberCitySharedFunctionality::dataTransfer(String dataToPost, String apiKeyValue, String sensorName, String sensorLocation, int delayBetweenPosts, String serverName, boolean postData, boolean readData)
{
  String payload;
  if (WiFi.status() == WL_CONNECTED)
  {
    WiFiClient client;
    HTTPClient http;

    // Your Domain name with URL path or IP address with path
    http.begin(client, serverName);

    // Specify content-type header
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Prepare your HTTP POST request data
    // String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorName + "&location=" + sensorLocation + "&sensorValue=" + dataToPost;
    // Serial.print("httpRequestData: ");
    // Serial.println(httpRequestData);

    // Send HTTP POST request, and store response code
    //int httpResponseCode = http.POST(httpRequestData);
    http.addHeader("Content-Type", "application/json");
    String postJSONString = "{\"api_key\":\""+apiKeyValue+"\",\"sensor\":\""+sensorName+"\",\"location\":\""+sensorLocation+"\",\"sensorValue\":\""+dataToPost+"\"}";
    Serial.print("Debug JSON String: ");
    Serial.println(postJSONString);
    int httpResponseCode = http.POST(postJSONString);

    // Get the HTML response from the server.
    payload = http.getString();
 
    if (httpResponseCode > 0)
    {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    }
    else
    {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    // Free resources
    http.end();
  }
  else
  {
    Serial.println("WiFi Disconnected");
  }
  // Send an HTTP POST request every 30 seconds
  delay(delayBetweenPosts);
  return payload;
}
