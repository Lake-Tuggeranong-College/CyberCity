<?php
    include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
}

if (isset($_GET["moduleID"])) {
    $challengeToLoad = $_GET["moduleID"];
} else {
    header("location:challengesList.php");
}

$sql = $conn->query("SELECT ID, moduleID, challengeTitle, challengeText, PointsValue, HashedFlag FROM Challenges WHERE moduleID = " . $challengeToLoad . " ORDER BY ID DESC");
$result = $sql->fetch();
$challengeID = $result["ID"];
$moduleID = $result["moduleID"];
$title = $result["challengeTitle"];
$challengeText = $result["challengeText"];
$pointsValue = $result["PointsValue"];
$hashedFlag = $result["HashedFlag"];
//print_r($hashedFlag);

$moduleQuery = $conn->query("SELECT Image from RegisteredModules WHERE ID = $moduleID");
$moduleInformation = $moduleQuery->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEnteredFlag = sanitise_data($_POST['hiddenflag']);
    //    $challengeToLoad = $_GET["moduleID"];
    //    $flagList = $conn->query("SELECT HashedFlag, PointsValue, moduleID, challengeTitle, challengeText, PointsValue FROM Challenges WHERE moduleID = " . $challengeToLoad . "");
    //
    //    while ($flagData = $flagList->fetch()) {
//                if (password_verify($userEnteredFlag, $hashedFlag)) {
    if ($userEnteredFlag == $hashedFlag) {
        $user = $_SESSION["user_id"];
        $query = $conn->query("SELECT * FROM `UserChallenges` WHERE `challengeID` ='$challengeID' AND `userID` = '$user'");
        $row = $query->fetch();
        if ($query->rowCount() > 0) {
            $_SESSION["flash_message"] = "<div class='bg-warning'>Flag Success! Challenge already completed, no points awarded</div>";
            header("Location:./challengesList.php");
        } else {
            $insert = "INSERT INTO `UserChallenges` (userID, challengeID) VALUES ('$user', '$challengeID')";
            $insert = $conn->prepare($insert);
            $insert->execute();

            $sql = "UPDATE Users SET Score = SCORE + '$pointsValue' WHERE ID='$user'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $_SESSION["flash_message"] = $query->rowCount();
            $userInformation = $conn->query("SELECT Score FROM Users WHERE ID='$user'");
            $userData = $userInformation->fetch();
            $addedScore = $userData["Score"] += $pointsValue;
            $sql1 = "UPDATE Users SET Score=? WHERE Username=?";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$addedScore, $user]);

            if ($moduleID == 19) {
                $sql = "UPDATE RegisteredModules SET CurrentOutput = 'On' WHERE ID='$moduleID'";
            }
            else {
                $sql = "UPDATE RegisteredModules SET CurrentOutput = CurrentOutput + '1' WHERE ID='$moduleID'";
            }
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $_SESSION["flash_message"] = "<div class='bg-success'>Success!</div>";
            header("Location:./challengesList.php");
        }
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger'>Flag failed - Try again</div>";
        header('Location: '. $_SERVER['REQUEST_URI']);
        die;
    }
}

?>

<title>Challenge Information</title>

</head>

<body>
<!-- Indicate heading secion of the whole page. -->
<header class="container-fluid d-flex align-items-center justify-content-center">
    <h1 class="text-uppercase">Challenge - <?= $title ?></h1>
</header>

<!-- Indicate section (middle part) section of the whole page. -->
<section class="pt-4 pd-2" style="padding: 10px;">
    <!-- Boostrap Grid Table System. -->

    <div class="container-fluid text-center">

        <div class="row border border-dark-subtle border-2">
             <div class="col-2 border-start border-end border-dark-subtle border-2">
                Challenge Image
            </div>
            <div class="col-2 border-start border-end border-dark-subtle border-2">

                Challenge Name
            </div>
           <div class="col-7 border-start border-end border-dark-subtle border-2">
                Challenge Description
            </div>
            <div class="col-1 border-start border-end border-dark-subtle border-2">
                Challenge Points
            </div>
        </div>

        <div class="row border border-top-0 border-dark-subtle border-2">
            <div class="col-2 border-start border-end border-dark-subtle border-2">

                <?php
                if ($moduleInformation['Image']) {
                    // Display Module Image.
                    echo "<div class='image'><img src='" . BASE_URL . "assets/img/challengeImages/" . $moduleInformation['Image'] . " ' width='100' height='100'></div>";
                } else {
                    // Display Placeholder Image
                    echo "<div class='image'><img src='" . BASE_URL . "assets/img/challengeImages/Image Not Found.jpg' width='100' height='100'></div>";
                }
                ?>
            </div>
            <div class="col-2 fw-bold d-flex align-items-center justify-content-center">

                <?= $title ?>
            </div>
            <div class="col-7 border-start border-end border-dark-subtle border-2">
                <?= $challengeText ?>
            </div>
            <div class="col-1 d-flex align-items-center justify-content-center">
                <?= $pointsValue ?>
            </div>
        </div>

        <div class="row border border-top-0 border-dark-subtle border-2">
            <p class="text-success fw-bold pt-3">Good luck and have fun!</p>
        </div>

        <!-- Inline CSS styling for Horizontal line. -->
        <hr style="
                    border: none; 
                    position: relative; 
                    margin: 1.5rem 0; 
                    height: 4px; /* Adjust horizontal line thickness.*/
                    color: red; /* Compatible for users using older version of any Web Browser Apps.*/
                    background-color: red;
                ">

        <!-- Directs to correspond page if the flag entered is eligible. -->
        <form action="challengeDisplay.php?moduleID=<?= $moduleID ?>" method="post" enctype="multipart/form-data">
            <div class="form-floating">
                <input type="text" class="flag-input" id="flag" name="hiddenflag" placeholder="CTF{Flag_Here}">
<!--                <label for="flag">Please enter the flag: </label>-->
                <p id="functionAssistant" class="form-text text-start font-size-sm">
                    You'll have to hit the "Enter" key when finish
                    entering the hidden flag.
                </p>
            </div>

        </form>
</section>

<!-- Indicate footer (end part) section of the whole page. -->
<footer style="padding: 10px;">
    <h2 class='ps-3'>Recent Data</h2>

    <!-- Boostrap Grid Table System. -->
    <div class="container-fluid" >
        <div class="row border text-center">
            <div class="col border-end">Data & Time</div>
            <div class="col">Data</div>
        </div>

        <!--
            TODO: I do need test on this as I'm editing this PHP part thorugh my local PC (which cannot access the Cyber Range IP network
            unless this project is built thorugh a HTTPS provider).
         -->
        <!-- Automatically create new row to display ESP32 modules data & logged time on the specific challege webpage. -->
        <?php

        // Ryan's Module - Do not change under pain of death. Or at least a stern talking to.
        if ($moduleID == 43) {
            $sql = $conn->query("SELECT * FROM ModuleData WHERE moduleID = " . $challengeToLoad . " ORDER BY id DESC LIMIT 10");
        } else {
            $sql = $conn->query("SELECT * FROM ModuleData WHERE moduleID = " . $challengeToLoad . " ORDER BY id DESC LIMIT 5");
        }
        while ($moduleIndividualData = $sql->fetch()) {
            echo "<div class='row border border-top-0'>";

            // $moduleInformation = $sql->fetch();
            $moduleData = $moduleIndividualData["Data"];
            $moduleDateTime = $moduleIndividualData["DateTime"];

            echo "<div class='col border-end text-center'>" . $moduleDateTime . "</div>";
            echo "<div class='col text-center'>" . $moduleData . "</div>";
            echo "</div>";
        }
        ?>
    </div>
</footer>

</body>
</html>