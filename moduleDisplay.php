<?php include "template.php";
/** @var $conn */ ?>


</head>

<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
} else {
    header("location:moduleList.php");
}

?>

<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">Date & Time</div>
        <div class="col-6">Data</div>

    </div>


    <?php
    $sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData WHERE ModuleID = " . $moduleToLoad . " ORDER BY EventID DESC";

    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row_id = $row["EventID"];
            $row_moduleID = $row["ModuleID"];
            $row_dateTime = $row["DateTime"];
            $row_data = $row["Data"];

            ?>
            <div class="row">

                <div class="col-6"> <?= $row_dateTime ?> </div>
                <div class="col-6"> <?= $row_data ?> &#8451</div>
            </div>

            <?php

        }
        $result = null;
    }
    ?>
</div>
<?php echo outputFooter(); ?>
