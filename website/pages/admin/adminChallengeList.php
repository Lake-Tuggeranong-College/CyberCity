<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
}

?>

    <title>Challenges List</title>
</head>

    <h1>Challenges List</h1>

<?php
$moduleList = $conn->query("SELECT ChallengeTitle, ID, Enabled, Image FROM Challenges WHERE Enabled=1"); #Get all Enabled Modules
while ($moduleData = $moduleList->fetch()) {
    $moduleID = $moduleData["ID"];
    echo "<div class='product_wrapper'>";
    if ($moduleData['Image']) { #Does the Module have an Image?
        echo "<div class='image'><a href='adminChallengeInformation.php?ChallengeID=" . $moduleID . "'><img src='../../assets/img/challengeImages/" . $moduleData['Image'] . "' width='100' height='100'/></a></div>"; #Display Module Image
    } else {
        echo "<div class='image'><a href='adminChallengeInformation.php?ChallengeID=" . $moduleID . "'><img src='../../assets/img/challengeImages/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
    }
    echo "
        <div class='name'>" . $moduleData[0] . "</div>
        <div class='price'> $moduleData[1]</div>
        ";
    if ($_SESSION["access_level"] == 2) {
        echo "
                <a class='moduleButton' href='adminChallengeInformation.php?ChallengeID=" . $moduleID . "'>Information</a>
                <a class='moduleButton' href='adminChallengeEdit.php?ChallengeID=" . $moduleID . "'>Edit</a>";
    }
    echo "</div>";
}
