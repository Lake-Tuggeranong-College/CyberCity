<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
}

?>
<div class = 'wideBox'>
         <div class = 'title'>
    <title>Cyber City - Challenges</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/moduleList.css">




    <h1>Challenges</h1>


    <a href="backupDieselGenerators.php">custom challenge webpage test</a>

    <?php
        // Get all Enabled Modules.
        $moduleList = $conn->query("SELECT ID, challengeTitle,PointsValue,moduleID FROM Challenges");

        while ($challengeData = $moduleList->fetch()) {

            $challengeID = $challengeData["ID"];
            $moduleID = $challengeData["moduleID"];
            $moduleQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
            $moduleInformation = $moduleQuery->fetch();
            echo "<a href='challengeDisplay.php?moduleID=" . $moduleID . "'><div class='product_wrapper'>";





            
            // Check if the "Modules" have an image attached to it.
            if ($moduleInformation['Image']) { 
                // Display Module Image.
                echo "<div class='image'><img src='" . BASE_URL ."assets/img/challengeImages/" . $moduleInformation['Image'] . " ' width='100' height='100'></div>";
            } else {
                // Display Placeholder Image
                echo "<div class='image'><img src='" . BASE_URL ."assets/img/challengeImages/Image Not Found.jpg' width='100' height='100'></div>";
            }
    ?>

    <div class='name'><?=$challengeData['challengeTitle']?> </div>
    <div class='price'> Points: <?=$challengeData['PointsValue']?> </div>


    </div>
    <?php } ?>
</div>
<br>