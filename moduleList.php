<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>

    <title>Module List</title>
    <link rel="stylesheet" href="css/moduleList.css">

    <h1>Module List</h1>

<?php
$moduleList = $conn->query("SELECT Location, Module, ID, Enabled, Image FROM RegisteredModules WHERE Enabled=1"); #Get all Enabled Modules
while ($moduleData = $moduleList->fetch()) {
    $moduleID = $moduleData["ID"];
    echo "<div class='product_wrapper'>";
    if ($moduleData['Image']) { #Does the Module have an Image?
        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $moduleID . "'><img src='images/modules/" . $moduleData['Image'] . "' width='100' height='100'/></a></div>"; #Display Module Image
    } else {
        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $moduleID . "'><img src='images/modules/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
    }
    echo "
        <div class='name'>" . $moduleData[0] . "</div>
        <div class='price'> $moduleData[1]</div>
        ";
    if ($_SESSION["access_level"] == 2) {
        echo "
                <a class='moduleButton' href='moduleInformation.php?ModuleID=" . $moduleID . "'>Information</a>
                <a class='moduleButton' href='moduleEdit.php?ModuleID=" . $moduleID . "'>Edit</a>";
    }
    echo "</div>";
}
