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

   echo "<title> Module ID:" .$row_moduleID.  "</title>";
                echo "<tr>";

                echo "<td>" . $row_dateTime . "</td>";
                echo "<td>" . $row_data .  "  </td>";
                echo "</tr>";

        }
        $result = null;
    }
    ?>
</div>
<?php echo outputFooter(); ?>
