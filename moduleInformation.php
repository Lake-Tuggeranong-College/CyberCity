<?php include "template.php";
/** @var $conn */


?>



<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
} else {
    header("location:moduleList.php");
}

$sql= $conn->query("SELECT ID, Location, Module FROM RegisteredModules WHERE ID= .$moduleToLoad. ");
$moduleInformation = $sql->fetch();
    $moduleID = $moduleInformation[0];
    $moduleLocation = $moduleInformation[1];
    $moduleName = $moduleInformation[2];





?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <h3>Module Name <?php echo $moduleName; ?></h3>
        </div>
















