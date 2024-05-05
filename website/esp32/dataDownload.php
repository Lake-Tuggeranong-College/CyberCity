<?php

//include "template.php";
include "config.php";
include "dataCommon.php";
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


    downloadData();

} else {
    echo "This page is only accessible via POSTing from ESP32s";
//    header("Location:index.php");
//    $_SESSION['flash_message'] = "<div class='bg-danger'>No post data sent</div>";
}

