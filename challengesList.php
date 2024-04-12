<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>
<div>
    <title>Cyber City - Challenges</title>
    <link rel="stylesheet" href="css/moduleList.css">

    <h1 >Challenge List</h1>

    <?php
        // Get all Enabled Modules.
        $moduleList = $conn->query("SELECT ID, challengeTitle,PointsValue,moduleID FROM Challenges");

        while ($challengeData = $moduleList->fetch()) {

            $challengeID = $challengeData["ID"];
            $moduleID = $challengeData["moduleID"];
            $moduleQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
            $moduleInformation = $moduleQuery->fetch();
            echo "<a href='challengeDisplay.php?moduleID=" . $moduleID . "'><div class='product_wrapper'>";
            
            // Check if the "Modules" have an image attachs to it.
            if ($moduleInformation['Image']) { 
                // Display Module Image.
                echo "<div class='image'><img src='images/modules/" . $moduleInformation['Image'] . " ' width='100' height='100'></div>";
            } else {
                // Display Placeholder Image
                echo "<div class='image'><img src='images/modules/Image Not Found.jpg' width='100' height='100'></div>"; 
            }
    ?>

    <div class='name'><?=$challengeData['challengeTitle']?> </div>
    <div class='price'> Points: <?=$challengeData['PointsValue']?> </div>
    </div>
    <?php } ?>

</html>
<br>