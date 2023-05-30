<?php include "template.php";
/** @var $conn */
if ($_SESSION["access_level"] != 2) {
    header("Location:index.php");
    $_SESSION['flash_message'] = "<div class='bg-danger'>Access Denied</div>";
}

?>

<title>Module Information List</title>


<h1> Module Information list</h1>


<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
} else {
    header("location:moduleList.php");
}

$sql= $conn->query("SELECT ID, Location, Module, CurrentOutput FROM RegisteredModules WHERE ID= '$moduleToLoad' ");
$moduleInformation = $sql->fetch();
$moduleID = $moduleInformation["ID"];
$moduleLocation = $moduleInformation["Location"];
$moduleName = $moduleInformation["Module"];
$moduleOutput = $moduleInformation["CurrentOutput"];
?>

<!--<h1 class='text-primary'>Module Name <?php /*echo $moduleName; */?></h1>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <?php /*= $moduleName */?>
        </div>
        <div class="col-md-4">
            <?php /*= $moduleLocation */?>
        </div>
        <div class="col-md-4">
            <?php /*= $moduleOutput */?>
        </div>
    </div>
</div>
-->

<?= outputFooter() ?>

<?php
/*echo '<h2 class="text-danger">Debug Information. Comment out as necessary</h2><pre>';
print_r($moduleInformation);
echo '</pre>';


*/?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <h3>Module Name: <?php echo $moduleName; ?></h3>
            <h3>Module Location: <?php echo $moduleLocation; ?></h3>
            <h3>Module ID: <?php echo $moduleID; ?> </h3>
            <h3>Current Output: <?php echo $moduleOutput; ?> </h3>
        </div>
















