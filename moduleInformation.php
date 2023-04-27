<?php include "template.php";
/** @var $conn */


?>



<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
} else {
    header("location:moduleList.php");
}

$sql = $conn->query("SELECT ID, Location, Module, CurrentOutput FROM RegisteredModules WHERE ID= " . $moduleToLoad);
$moduleInformation = $sql->fetch();
$moduleID = $moduleInformation["ID"];
$moduleLocation = $moduleInformation["Location"];
$moduleName = $moduleInformation["Module"];
$moduleOutput = $moduleInformation["CurrentOutput"];
?>

<h1 class='text-primary'>Module Name <?php echo $moduleName; ?></h1>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <?= $moduleName ?>
        </div>
        <div class="col-md-4">
            <?= $moduleLocation ?>
        </div>
        <div class="col-md-4">
            <?= $moduleOutput ?>
        </div>
    </div>
</div>


<?= outputFooter() ?>

<?php
echo '<h2 class="text-danger">Debug Information. Comment out as necessary</h2><pre>';
print_r($moduleInformation);
echo '</pre>';


?>


</html>
</head>














