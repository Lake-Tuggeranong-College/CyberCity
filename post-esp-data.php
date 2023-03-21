<?php include "template.php";
/** @var $conn */

/*
  Rui Santos
  Complete project details at https://RandomNerdTutorials.com/esp32-esp8266-mysql-database-php/

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files.

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.
*/



// Keep this API Key value to be compatible with the ESP32 code provided in the project page.
// If you change this value, the ESP32 sketch needs to match

$api_key= $sensor = $location = $sensorValue = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = sanitise_data($_POST["api_key"]);
    $location = sanitise_data($_POST["location"]);
    $query = $conn->query("SELECT COUNT(*) as count FROM `RegisteredModules` WHERE `Location` ='$location'");
    $row = $query->fetch();
    $count = $row[0];

    if ($count > 0) {
        $query = $conn->query("SELECT * FROM `RegisteredModules` WHERE `Location`='$location'");
        $row = $query->fetch();
        $api_key_value = $row[3];
        if (password_verify($api_key, $api_key_value)) {
            $sensorValue = sanitise_data($_POST["sensorValue"]);
            date_default_timezone_set('Australia/Canberra');
            $date = date("Y-m-d h:i:sa");
            $ModuleID = $row[0];

            $sql = "INSERT INTO ModuleData (ModuleID, DateTime, Data) VALUES (:ModuleID, :date, :sensorValue)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':ModuleID', $ModuleID);
            $stmt->bindValue(':date', $date);
            $stmt->bindValue(':sensorValue', $sensorValue);
            $stmt->execute();

            if ($conn->query($sql) === TRUE) {
                echo "New record created successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
            $conn->close();
        } else {
            echo "Wrong API Key provided.";
        }
    } else {
        echo "No data posted with HTTP POST.";
    }
}

