<?php

//include "template.php";
include "config.php";
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

    // Takes raw data from the request
    $json = file_get_contents('php://input');
    // Converts it into a PHP object
    $data = json_decode($json);

    switch (json_last_error()) {
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
    }

    // example of extracting one element of json object
    $api_key = $data->api_key;
    $location = $data->location;

    // encode it again so it can be returned...
//    header('Content-Type: application/json; charset=utf-8');
//    echo json_encode($data);

//    $api_key = sanitise_data($api);
//    $location = sanitise_data($loc);
//   echo "Location: ".$location;
    $query = $conn->query("SELECT COUNT(*) as count FROM RegisteredModules WHERE Location ='$location'");

    $row = $query->fetch();
    $count = $row[0];
    if ($count > 0) {
        $query = $conn->query("SELECT * FROM RegisteredModules WHERE Location='$location'");
        $row = $query->fetch();
        $payload = $row[4];
        $api_key_value = $row[3];
//        echo "verifying...";
//        if (password_verify($api_key, $api_key_value)) {
//            $sensorValue = sanitise_data($_POST["sensorValue"]);
        $sensorValue = $data->sensorValue;
        date_default_timezone_set('Australia/Canberra');
        $date = date("Y-m-d H:i:s");
        //DO NOT CHANGE THIS DATE CODE, MUST STAY SAME TO WORK WITH MYSQL
        $ModuleID = $row[0];
//            echo "inserting....";
        $sql = "INSERT INTO ModuleData (ModuleID, DateTime, Data) VALUES (:ModuleID, :date, :sensorValue)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':ModuleID', $ModuleID);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':sensorValue', $sensorValue);
        $stmt->execute();

        //convert server command to JSON for return to ESP
        $payloadJSON = ['command' => $payload];
        header('Content-type: application/json');
        echo json_encode($payloadJSON);
        $conn->close();
//        } else {
//            echo "API Key incorrect";
//        }
    } else {
        echo "Module not found!!";
    }
} else {
    echo "This page is only accessible via POSTing from ESP32s";
//    header("Location:index.php");
//    $_SESSION['flash_message'] = "<div class='bg-danger'>No post data sent</div>";
}

