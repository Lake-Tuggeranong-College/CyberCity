<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>

    <title>userList</title>
    <link rel="stylesheet" href="css/moduleList.css">

    <h1 class='text-primary'>userList</h1>

<?php
$userList = $conn->query("SELECT ID, Username, AccessLevel, Enabled FROM Users WHERE Enabled=1"); #Get all Enabled Modules
while ($userData = $userList->fetch()) {
    $userID = $userData["ID"];
    echo "<div class='product_wrapper'>";
//    if ($userData['Image']) { #Does the Module have an Image?
//        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $userID . "'><img src='images/modules/" . $userData['Image'] . "' width='100' height='100'/></a></div>"; #Display Module Image
//    } else {
//        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $userID . "'><img src='images/modules/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
//    }
    echo "
        <div class='name'>" . $userData["Username"] . "</div>
        <div class='price'>". $userData["AccessLevel"]."</div>
        ";
    if ($_SESSION["access_level"] == 2) {
        echo "
                <a class='moduleButton' href='moduleInformation.php?ModuleID=" . $userID . "'>Information</a>
                <a class='moduleButton' href='moduleEdit.php?ModuleID=" . $userID . "'>Edit</a>";
    }
    echo "</div>";

}


$userList = $conn->query("SELECT ID, Username, AccessLevel, Enabled FROM Users WHERE Enabled=0"); #Get all Enabled Modules
while ($userData = $userList->fetch()) {
    $userID = $userData["ID"];
    echo "<div class='product_wrapper'>";
//    if ($userData['Image']) { #Does the Module have an Image?
//        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $userID . "'><img src='images/modules/" . $userData['Image'] . "' width='100' height='100'/></a></div>"; #Display Module Image
//    } else {
//        echo "<div class='image'><a href='moduleDisplay.php?ModuleID=" . $userID . "'><img src='images/modules/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
//    }
    echo "
        <div class='name'>" . $userData["Username"] . "</div>
        <div class='price'>". $userData["AccessLevel"]."</div>
        ";
    if ($_SESSION["access_level"] == 2) {
        echo "
                <a class='moduleButton' href='moduleInformation.php?ModuleID=" . $userID . "'>Information</a>
                <a class='moduleButton' href='moduleEdit.php?ModuleID=" . $userID . "'>Edit</a>";
    }
    echo "</div>";
}
