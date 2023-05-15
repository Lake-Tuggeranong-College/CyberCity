<?php include "template.php";
/** @var $conn */ ?>


<title>Module List</title>
    <link rel="stylesheet" href="css/moduleList.css">

<h1 class='text-primary'>Module List</h1>

<?php
$moduleList = $conn->query("SELECT Location, Module, ID, Enabled, Image FROM RegisteredModules");
?>

<?php

    while ($moduleData = $moduleList->fetch() ) {
        $moduleID = $moduleData["ID"];
        echo "<div class='product_wrapper'>
        <div class='image'><img src='images/modules/" . $moduleData['Image'] . "' width='100' height='100'/></div>
        <div class='name'>" . $moduleData[0] . "</div>
        <div class='price'> $moduleData[1]</div>
        ";
if ($_SESSION["access_level"] == 2) {
    echo"
                <a class='moduleButton' href='moduleInformation.php?ModuleID=" . $moduleID . "'>Information</a>
                <a class='moduleButton' href='moduleEdit.php?ModuleID=" . $moduleID . "'>Edit</a>
 
    </div>";
        }
    }
