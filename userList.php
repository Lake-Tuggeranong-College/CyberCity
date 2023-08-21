<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>
    <title>User List</title>
    <link rel="stylesheet" href="css/moduleList.css">
    <h1 class='text-primary'>User List</h1>
<?php
$userList = $conn->query("SELECT ID, Username, AccessLevel, Enabled FROM Users WHERE Enabled=1"); #Get all Enabled Modules
while ($userData = $userList->fetch()) {
    $userID = $userData["ID"];
    echo "<div class='product_wrapper'>";
    echo "<a class='moduleButton' href='userEdit.php?ModuleID=" . $userID . "'>Edit - " . $userData["Username"] . "</a>";
    echo "</div>";
}


$userList = $conn->query("SELECT ID, Username, AccessLevel, Enabled FROM Users WHERE Enabled=0"); #Get all Enabled Modules
while ($userData = $userList->fetch()) {
    $userID = $userData["ID"];
    echo "<div class='product_wrapper'>";
    echo "
        <div class='name'>" . $userData["Username"] . "</div>
        <div class='price'>" . $userData["AccessLevel"] . "</div>
        ";
    if ($_SESSION["access_level"] == 2) {
        echo "
                <a class='moduleButton' href='moduleInformation.php?ModuleID=" . $userID . "'>Information</a>
                <a class='moduleButton' href='moduleEdit.php?ModuleID=" . $userID . "'>Edit</a>";
    }
    echo "</div>";
}
