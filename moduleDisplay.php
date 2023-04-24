<?php include "template.php";
/** @var $conn */ ?>

<!DOCTYPE html>
<html>
<head>

</head>
<body>


<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
           // <th>Module</th>
            <th>Date & Time</th>
            <th>Data</th>

        </thead>
</body>
</html>

        <?php
        if (isset($_GET["ModuleID"])) {
            $moduleToLoad = $_GET["ModuleID"];
        } else {
    header("location:moduleList.php");
}

        $sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData WHERE ModuleID = ".$moduleToLoad." ORDER BY EventID DESC";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $row_id = $row["EventID"];
                $row_moduleID = $row["ModuleID"];
                $row_dateTime = $row["DateTime"];
                $row_data = $row["Data"];


                echo "<tr>";

                echo "<td>" . $row_dateTime . "</td>";
                echo "<td>" . $row_data . "</td>";
                echo "</tr>";

            }
            $result = null;
        }
        ?>
