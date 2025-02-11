<?php include "../../includes/template.php";
/** @var $conn */


if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
}



?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/moduleList.css">


<h1>Challenges</h1>
<?php
if (isset($_GET["projectID"])) {
    $projectID = $_GET["projectID"];
} else {
    header("location:index.php");
}
//function createCategoryHeader($conn, $categoryData) {
//   // Extract each field from the data array
//    $categoryName = $categoryData['categoryName'];
//echo $categoryData['categoryName'];
//    ?>
<!--    <h2>--><?php //= $categoryName ?><!--</h2>-->
<!--    --><?php



function createChallengeCard($conn, $completionStatus, $challengeData) {
    //Extract each field from the data array
    $challengeID = $challengeData['ID'];
    $challengeTitle = $challengeData['challengeTitle'];
    $pointsValue = $challengeData['PointsValue'];
    $moduleID = $challengeData['moduleID'];
    $container_required = $challengeData['container'];
    $projectID = $_GET["projectID"];

    // Check whether the challenge has an image
    $imageQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
    $imageData = $imageQuery->fetch();

    if ($imageData['Image']) {
        // Display Module Image.
        ?>
        <div class="product_wrapper">
            <div class="card <?php if ($completionStatus == TRUE) {?> text-bg-success <?php } else if ($completionStatus == FALSE) {?> text-bg-secondary <?php } else {throw new Error("Something has gone SERIOUSLY wrong!");} ?>" style="width: 18rem;">
                <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= $imageData['Image'] ?>"
                     class="card-img-top" alt="..." width="100" height="200">
                <div class="card-body">
                    <h5 class="card-title"><?= $challengeData["challengeTitle"] ?></h5>
                    <p class="card-text"><?= $challengeData["PointsValue"] ?></p>
                    <a href="<?php if ($container_required == 1) {?>challengeDisplayDocker.php?moduleID=<?php } else {?>challengeDisplay.php?moduleID= <?php } ?><?= $moduleID ?>" class="btn btn-warning">Start
                        Challenge</a>
                </div>
            </div>
        </div>

        <?php

    } else {
        // Display Placeholder Image
        ?>
        <div class='image'><img
                    src="<?= BASE_URL ?>assets/img/challengeImages/Image Not Found.jpg"
                    width='100' height='100'>
        </div>

        <?php
    }
}
$categoryQuery = $conn->query("SELECT CategoryName FROM category WHERE projectID = $projectID");
$categoryList = $categoryQuery->fetchAll( PDO::FETCH_ASSOC );
for ($catcount = 0; $catcount < sizeof($categoryList); $catcount++) {

    $categoryData = $categoryList[$catcount];
    ?>
    <div class="product_wrapper">
        <br>

    <h2 style="text-align: left;, width: 95%;, padding: 50px"><?= $categoryData['CategoryName']  ?></h2>
    <?php




    $userID = $_SESSION['user_id'];
    $challengeListQuery = $conn->query("SELECT ID, challengeTitle, PointsValue, moduleID, container FROM Challenges WHERE Enabled = 1 AND category = '$categoryData[CategoryName]'AND projectID = $projectID");
    $challengeList = $challengeListQuery->fetchAll(PDO::FETCH_ASSOC);
    for ($counter = 0; $counter < sizeof($challengeList); $counter++) {
        // Get the challenge data for the current iteration.
        // $counter is the index of the current challenge in the $challengeList array.
        // $challengeList is an array that contains the array of data for each challenge.
        // $challengeData is an associative array containing the challenge's ID, title, points value, and module ID.

        $challengeData = $challengeList[$counter];
        $challengeID = $challengeData['ID'];

        //Get completion status of the challenge
        $completionQuery = $conn->query("SELECT * FROM UserChallenges WHERE userID = '$userID' AND challengeID = '$challengeID'");
        if ($completionQuery->rowCount() > 0) {
            createChallengeCard($conn, TRUE, $challengeData);
        } else {
            createChallengeCard($conn, FALSE, $challengeData);
        }
    }
 ?>
    </div>
        <?php
}
?>

<!-- CUSTOM WEBPAGES GO HERE -->
<!--            <!-- <div class='product_wrapper' style='text-align: center;'><a href='backupDieselGenerators.php'>-->
<!--                 <!-- CUSTOM WEBPAGE CHALLENGE TEST -->
<!--                 <div class='image'><img style= 'width: 100px; height: 100px' src='../../assets/img/challengeImages/toilet.jpg'</img></div>-->
<!--                 <a>Custom Webpage Challenge Test</a>-->
<!--                 <p>Points: 0</p>-->
<!--             </a></div>-->
<!---->
<!--             <div class='product_wrapper' style='text-align: center;'><a href='biolabShutdown.php'>-->
<!--                 <!-- BIOLAB SHUTDOWN TEST-->
<!--                 <div class='image'><img style= 'width: 100px; height: 100px' src='../../assets/img/challengeImages/Biolab.png'</img></div>-->
<!--                 <a>Biolab Shutdown</a>-->
<!--                 <p>Points: 500</p>-->
<!--             </a></div>-->


</div>
</body>



