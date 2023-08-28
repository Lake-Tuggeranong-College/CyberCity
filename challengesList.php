<?php include "template.php";
/** @var $conn */
?>
    <title>Cyber City - Challenges</title>
    <link rel="stylesheet" href="css/moduleList.css">

    <h1 class='text-primary'>Challenge List</h1>


<?php
$moduleList = $conn->query("SELECT ID, challengeTitle,PointsValue,moduleID FROM Challenges"); #Get all Enabled Modules
while ($challengeData = $moduleList->fetch()) {
    $challengeID = $challengeData["ID"];
    $moduleID = $challengeData["moduleID"];
    $moduleQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
    $moduleInformation = $moduleQuery->fetch();
    echo "<div class='product_wrapper'>";
    if ($moduleInformation['Image']) { #Does the Module have an Image?
        echo "<div class='image'><a href='challengeDisplay.php?moduleID=" . $moduleID . "'><img src='images/modules/" . $moduleInformation['Image'] . "' width='100' height='100'/></a></div>"; #Display Module Image
    } else {
        echo "<div class='image'><a href='challengeDisplay.php?moduleID=" . $moduleID . "'><img src='images/modules/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
    }
    echo "
        <div class='name'>" . $challengeData[0] . "</div>
        <div class='price'>$challengeData[1] Points</div>
        ";
    echo "</div>";
}


