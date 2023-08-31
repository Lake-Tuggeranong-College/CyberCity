<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>


<!--<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>

            <th>Date & Time</th>
            <th>Data</th>

        </thead>-->

<html>
<head>
    <style>
        #myDiv {
            color: red;
        }
    </style>
</head>
<body>


<h1>Module - <?= $row_moduleID ?></h1>
<div class="container-fluid">
    <div class="row">
        <div class="col-1 border border border-dark">Module ID</div>
        <div class="col-1 border border border-dark">Module data</div>
        <div class="col-2 border border border-dark">Date & Time</div>
        <div class="col-1 border border border-dark">Event ID</div>

    </div>


</div>


</body>
</html>


        <?php
        if (isset($_GET["ModuleID"])) {
            $moduleToLoad = $_GET["ModuleID"];
        } else {
            header("location:moduleList.php");
        }

        $sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData WHERE ModuleID = " . $moduleToLoad . " ORDER BY EventID DESC";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $row_id = $row["EventID"];
                $row_moduleID = $row["ModuleID"];
                $row_dateTime = $row["DateTime"];
                $row_data = $row["Data"];

               /* echo "<title> Module ID:" . $row_moduleID . "</title>";
                echo "<tr>";

                echo "<td>" . $row_dateTime . "</td>";
                echo "<td>" . $row_data . "  </td>";
                echo "</tr>";*/



                echo "<div class='row'>";
                echo "<div class='col-1 border border border-dark''>" . $row_moduleID . "</div>";
                echo "<div class='col-1 border border border-dark''>" . $row_data . "</div>";
                echo "<div class='col-2 border border border-dark''>" . $row_dateTime. "</div>";
                echo "<div class='col-1 border border border-dark''>" . $row_id . "</div>";
                echo "</div>";



            }
            $result = null;
        }
        ?>





