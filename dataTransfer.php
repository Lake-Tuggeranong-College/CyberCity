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

$api_key = $sensor = $location = $sensorValue = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
//    echo "read";
    $api_key = sanitise_data($_POST["api_key"]);
    $location = sanitise_data($_POST["location"]);

    $query = $conn->query("SELECT COUNT(*) as count FROM `RegisteredModules` WHERE `Location` ='$location'");
    $row = $query->fetch();
    $count = $row[0];
    if ($count > 0) {
        $query = $conn->query("SELECT * FROM `RegisteredModules` WHERE `Location`='$location'");
        $row = $query->fetch();
        $payload = $row[4];
        $api_key_value = $row[3];
        if (password_verify($api_key, $api_key_value)) {
            $sensorValue = sanitise_data($_POST["sensorValue"]);
            date_default_timezone_set('Australia/Canberra');
            $date = date("Y-m-d H:i:s");
            //DO NOT CHANGE THIS DATE CODE, MUST STAY SAME TO WORK WITH MYSQL
            $ModuleID = $row[0];
            $sql = "INSERT INTO ModuleData (ModuleID, DateTime, Data) VALUES (:ModuleID, :date, :sensorValue)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':ModuleID', $ModuleID);
            $stmt->bindValue(':date', $date);
            $stmt->bindValue(':sensorValue', $sensorValue);
            $stmt->execute();
            echo "Payload:" . $payload;
            $conn->close();
        } else {

        }
    } else {
        echo "Module not found!!";
    }
} else {
    header("Location:index.php");
    $_SESSION['flash_message'] = "<div class='bg-danger'>No post data sent</div>";

}
