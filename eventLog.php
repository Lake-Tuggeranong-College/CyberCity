<?php
include "config.php";
/** @var $conn */

// Keep this API Key value to be compatible with the ESP32 code provided in the project page.
// If you change this value, the ESP32 sketch needs to match
$api_key = $userName = $eventData = "";


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
    // $api_key = $data->api_key;
    $userName = $data->userName;
    $eventData = $data->eventData;
    date_default_timezone_set('Australia/Canberra');
    $date = date("Y-m-d H:i:s");

    //DO NOT CHANGE THIS DATE CODE, MUST STAY SAME TO WORK WITH MYSQL
    $sql = "INSERT INTO eventLog (userName, eventText, datePosted) VALUES (:userName, :eventData, :datePosted)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':userName', $userName);
    $stmt->bindValue(':eventData', $eventData);
    $stmt->bindValue(':datePosted', $date);
    $stmt->execute();
    $conn->close();
} else {
    echo "This page is only accessible via POSTing from ESP32s..";
    echo "Your attempt has been logged.";
    $userName = "Unknown";
    $eventData = "Attempted to access eventLog.php via GET request.";
    date_default_timezone_set('Australia/Canberra');
    $date = date("Y-m-d H:i:s");
     $sql = "INSERT INTO eventLog (userName, eventText, datePosted) VALUES (:userName, :eventData, :datePosted)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':userName', $userName);
    $stmt->bindValue(':eventData', $eventData);
    $stmt->bindValue(':datePosted', $date);
    $stmt->execute();
    $conn->close();
}

