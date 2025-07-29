<?php include "../../includes/template.php";
/** @var $conn */


if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
}
//$projectID=0;

?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/moduleList.css">


<h1>Challenges</h1>

<div class="container-fluid>">

    <?php

    if (isset($_GET["projectID"])) {
        $projectID = $_GET["projectID"];
    } else {
        header("location:index.php");
    }

    function createChallengeCard($challengeData)
    {
        global $projectID, $conn;
        //Extract each field from the data array
        $challengeID = $challengeData['ID'];
        $challengeTitle = $challengeData['challengeTitle'];
        $pointsValue = $challengeData['pointsValue'];
        $moduleName = $challengeData['moduleName'];
        $container_required = $challengeData['dockerChallengeID'];
        $completionStatus = false; //TODO: Check Userchallenges to see if the user has already completed the challenge.
//    $projectID = $_GET["projectID"];

        // Check whether the challenge has an image
//    $imageQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
//    $imageQuery = $conn->query("SELECT Image from Challenges WHERE ID = $challengeID");
//    $imageData = $imageQuery->fetch();
        $imageFileName = $challengeData['Image'];

        // Is there an image?
        if ($imageFileName) {
            // Display Module Image.
            ?>
            <div class="product_wrapper">
                <div class="card <?php if ($completionStatus == TRUE) { ?> text-bg-success <?php } else if ($completionStatus == FALSE) { ?> text-bg-secondary <?php } else {
                    throw new Error("Something has gone SERIOUSLY wrong!");
                } ?>" style="width: 18rem;">
                    <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= $imageFileName ?>"
                         class="card-img-top" alt="..." width="100" height="200">
                    <div class="card-body">
                        <h5 class="card-title"><?= $challengeTitle ?></h5>
                        <p class="card-text"><?= $pointsValue ?></p>
                        <a href="<?php if (isset($container_required) ){ ?>challengeDisplayDocker.php?challengeID=<?php } else { ?>challengeDisplay.php?challengeID=<?php } ?><?= $challengeID ?>"
                           class="btn btn-warning">Start
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


    function displayResultsByCategory()
    {
        global $conn, $projectID;
        // This SQL query retrieves information about challenges related to a specific project, while also grouping them by category.
        $sql = "
        SELECT cat.CategoryName, ch.*
FROM Category AS cat
JOIN Challenges AS ch ON cat.id = ch.categoryID
JOIN ProjectChallenges AS pc ON ch.id = pc.challenge_id
JOIN Projects AS p ON pc.project_id = p.project_id
WHERE p.project_id = :project_id
ORDER BY cat.CategoryName;
        ";

        // Prepare and execute the query
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':project_id', $projectID, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Display the results grouped by category
        $currentCategory = null;
        echo "<div class='row'>";
        foreach ($results as $row) {
            // Display the category name as a heading
            if ($currentCategory !== $row['CategoryName']) {
                // Display category heading
                $currentCategory = $row['CategoryName'];
                echo "<h2>" . htmlspecialchars($currentCategory) . "</h2>";
            }
            // Display challenge data
//        echo "<p>" . htmlspecialchars($row['challengeTitle']) . "</p>";
            createChallengeCard($row);

        }
        echo "</div>";
    }



    function getChallengesForProject()
    {
        global $projectID, $conn;

        $sql = "
SELECT cat.CategoryName, ch.*
FROM Category AS cat
JOIN Challenges AS ch ON cat.id = ch.categoryID
JOIN ProjectChallenges AS pc ON ch.id = pc.challenge_id
JOIN Projects AS p ON pc.project_id = p.project_id
WHERE p.project_id = :project_id
ORDER BY cat.CategoryName;
        ";

        // Prepare and execute the query
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':project_id', $projectID, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($results);
        // Display the results grouped by category
        $currentCategory = null;
        foreach ($results as $row) {
            print_r($row);
            if ($currentCategory !== $row['CategoryName']) {
                // Display category heading
                $currentCategory = $row['CategoryName'];
                echo "<h2>" . htmlspecialchars($currentCategory) . "</h2>";
                createChallengeCard(true, $row);
            }
            // Display challenge data
//        print_r($row);
        }

    }



    // Call the function with a project ID
    displayResultsByCategory();

    //getChallengesForProject();

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



