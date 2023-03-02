
<?php
/*
  Rui Santos
  Complete project details at https://RandomNerdTutorials.com/esp32-esp8266-mysql-database-php/

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files.

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.
*/
/** @var TYPE_NAME $conn */
include "template.php";


$sql = "SELECT id, sensor, location, value1, reading_time FROM SensorData ORDER BY id DESC";

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-1">ID</div>
        <div class="col-md-1">Sensor</div>
        <div class="col-md-2">Location</div>
        <div class="col-md-2">Value 1</div>
<!--        <div class="col-md-2">Value 2</div>-->
<!--        <div class="col-md-2">Value 3</div>-->
        <div class="col-md-2">Timestamp</div>
    </div>


    <?php

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row_id = $row["id"];
            $row_sensor = $row["sensor"];
            $row_location = $row["location"];
            $row_value1 = $row["value1"];
//            $row_value2 = $row["value2"];
//            $row_value3 = $row["value3"];
            $row_reading_time = $row["reading_time"];
            echo '
    <div class="row">
        <div class="col-md-1">' . $row_id . '</div>
        <div class="col-md-1">' . $row_sensor . '</div>
        <div class="col-md-2">' . $row_location . '</div>
        <div class="col-md-2">' . $row_value1 . '</div>

        <div class="col-md-2">' . $row_reading_time . '</div>
    </div>
';
        }
        // $result->free();
        $result = null;
    } else {
        echo "Error in SQL";
    }

    //$conn->close();
    ?>
</div>
</body>
</html>