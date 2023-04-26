<?php include "template.php";
/** @var $conn */


?>

<title> Module information page</title>

<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
} else {
    header("location:moduleList.php");
}

?>