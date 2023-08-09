<?php include "template.php";
/** @var $conn */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cyber City - Challenges</title>
    <link rel="stylesheet" href="css/moduleList.css">
</head>
<body>
<h1 class='text-primary'>Challenge List</h1>


<?php
$moduleList = $conn->query("SELECT challengeTitle,PointsValue FROM Challenges"); #Get all Enabled Modules
while ($challangeData = $moduleList->fetch()) {
    $shallangeID = $challangeData["ID"];
    echo "<div class='product_wrapper'>";
    echo "
        <div class='name'>" . $challangeData[0] . "</div>
        <div class='price'>$challangeData[1] Points</div>
        ";
    echo "</div>";
}


