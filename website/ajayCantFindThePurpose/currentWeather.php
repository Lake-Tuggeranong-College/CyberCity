<?php include "template.php";
/** @var $conn */


$sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData ORDER BY EventID DESC";

?>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-2">  Event ID:</div>
        <div class="col-md-2">   Module ID:</div>
        <div class="col-md-4"> Date & Time:</div>
        <div class="col-md-4">  Data:</div>

    </div>
    <?php

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row_id = $row["EventID"];
            $row_moduleID = $row["ModuleID"];
            $row_dateTime = $row["DateTime"];
            $row_data = $row["Data"];
//            $row_value2 = $row["value2"];
//            $row_value3 = $row["value3"];
            echo '
    <div class="row">
        <div class="col-md-2"> '. $row_id . '</div>
        <div class="col-md-2"> ' . $row_moduleID . '</div>
        <div class="col-md-4"> ' . $row_dateTime . '</div>
        <div class="col-md-4"> ' . $row_data . ' C</div>
    </div>
    
';
        }
        // $result->free();
        $result = null;
    } else {
        echo "Error in SQL";
    }


    ?>
</div>


