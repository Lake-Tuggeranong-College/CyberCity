<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>

<head>
    <title>Cyber City - Challenges</title>
    <link rel="stylesheet" href="css/moduleList.css">
</head>
<body>
<h1 class='text-primary'>Learn</h1>

<?php
$learnList = $conn->query("SELECT ID,Name,Icon FROM Learn"); #Get all Enabled Modules
while ($learnData = $learnList->fetch()) {
    $learnID = $learnData["ID"];
    echo "<div class='product_wrapper'>";
    if ($learnData[2]) { #Does the Module have an Image?
        echo "<div class='image'><a href='tutorialPage.php?tutorialID=" . $learnData[0] . "'><img src='images/modules/" . $learnData[1] . "' width='100' height='100'/></a></div>"; #Display Module Image
    } else {
        echo "<div class='image'><a href='tutorialPage.php?tutorialID=" . $learnData[0] . "'><img src='images/modules/blank.jpg'width='100' height='100'/></a></div>"; #Display Placeholder Image
    }
    echo "
        <div class='name'>" . $learnData[1] . "</div>
        ";
    echo "</div>";
}
