<?php include "template.php";
/** @var $conn */ ?>

<title>Module List</title>

<h1 class='text-primary'>Module List</h1>

<?php
$moduleList = $conn->query("SELECT Location, Module, ID, Enabled FROM RegisteredModules");
?>

<div class="container-fluid">
    <?php
    while ($moduleData = $moduleList->fetch() ) {
        $moduleID = $moduleData["ID"];
        $EnabledModule = $moduleData["Enabled"];
        if ($EnabledModule == 1){
        ?>
        <div class="row">

            <div class="col-md-2">
                <a href="moduleDisplay.php?ModuleID=<?= $moduleID ?>"><?php echo $moduleData[0]; ?></a>
            </div>
            <div class="col-md-2">
                <?php echo $moduleData[1]; ?>
            </div>
            <?php
            if ($_SESSION["access_level"] == 2) {
                ?>

                <div class="col-md-2">
                    <a href="moduleInformation.php?ModuleID=<?= $moduleID ?>">Information</a>
                </div>
                <div class="col-md-2">
                    <a href="moduleEdit.php?ModuleID=<?= $moduleID ?>">Edit</a>
                </div>
                <?php
            }
            ?>

        </div>
        <?php
        }
    }
    ?>

    <?php echo outputFooter(); ?>
