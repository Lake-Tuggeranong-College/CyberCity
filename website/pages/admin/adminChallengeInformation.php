<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

?>

<title>Module Details</title>


<h1> Module Details</h1>


<?php
if (isset($_GET["ChallengeID"])) {
    $challengeToLoad = $_GET["ChallengeID"];
} else {
    header("location:adminChallengeList.php");
}

$sql = $conn->query("SELECT ID, challengeTitle, moduleValue FROM Challenges WHERE ID= '$challengeToLoad' ");
$moduleInformation = $sql->fetch();
$challengeName = $moduleInformation["challengeTitle"];
$moduleOutput = $moduleInformation["moduleValue"];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            Module Name
        </div>
        <div class="col-md-4">
            Module Output Value
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $challengeName ?>
        </div>
        <div class="col-md-4">
            <?= $moduleOutput ?>
        </div>
    </div>
</div>



