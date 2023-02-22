<?php

/*
  Rui Santos
  Complete project details at https://RandomNerdTutorials.com/esp32-esp8266-mysql-database-php/

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files.

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.
*/
include "template.php";


// Keep this API Key value to be compatible with the ESP32 code provided in the project page.
// If you change this value, the ESP32 sketch needs to match
$api_key_value = "tPmAT5Ab3j7F9";

$api_key= $sensor = $location = $value1 = $value2 = $value3 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = sanitise_data($_POST["api_key"]);
    if($api_key == $api_key_value) {
        $sensor = sanitise_data($_POST["sensor"]);
        $location = sanitise_data($_POST["location"]);
        $value1 = sanitise_data($_POST["value1"]);
//        $value2 = sanitise_data($_POST["value2"]);
//        $value3 = sanitise_data($_POST["value3"]);

        $sql = "INSERT INTO SensorData (sensor, location, value1)
        VALUES ('" . $sensor . "', '" . $location . "', '" . $value1 . "')";

        /** @var TYPE_NAME $conn */
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        }
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
    else {
        echo "Wrong API Key provided.";
    }

}
else {
    echo "No data posted with HTTP POST.";
}
