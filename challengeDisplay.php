<?php
include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

if (isset($_GET["moduleID"])) {
    $challengeToLoad = $_GET["moduleID"];
} else {
    header("location:challengesList.php");
}

$sql = $conn->query("SELECT moduleID, challengeTitle, challengeText, PointsValue, HashedFlag FROM Challenges WHERE moduleID = " . $challengeToLoad . " ORDER BY ID DESC");
$result = $sql->fetch();
$moduleID = $result["moduleID"];
$title = $result["challengeTitle"];
$challengeText = $result["challengeText"];
$pointsValue = $result["PointsValue"];
$hashedFlag = $result["HashedFlag"];
//print_r($hashedFlag);
?>

<title>Challenge Information</title>

</head>
<body>
<!-- Indicate heading secion of the whole page. -->
<header class="container-fluid d-flex align-items-center justify-content-center">
    <h1 class="text-uppercase">Challenge - <?= $title ?></h1>
</header>

<!-- Indicate section (middle part) section of the whole page. -->
<section class="pt-4 pd-2">
    <!-- Boostrap Grid Table System. -->

    <div class="container-fluid text-center">

        <div class="row border border-dark-subtle border-2">
            <div class="col fw-bold border-dark-subtle border-2">
                Challenge Name
            </div>
            <div class="col fw-bold border-start border-end border-dark-subtle border-2">
                Challenge Description
            </div>
            <div class="col fw-bold border-dark-subtle border-2">
                Challenge Points
            </div>
        </div>

        <div class="row border border-top-0 border-dark-subtle border-2">
            <div class="col fw-bold d-flex align-items-center justify-content-center">
                <?= $title ?>
            </div>
            <div class="col border-start border-end border-dark-subtle border-2">
                <?= $challengeText ?>
            </div>
            <div class="col d-flex align-items-center justify-content-center">
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
                <input type="text" class="form-control" id="flag" name="hiddenflag" placeholder="Enter the flag here.">
                <label for="flag">Please enter the flag: </label>
                <p id="functionAssistant" class="form-text text-start font-size-sm">
                    You'll have to hit the "Enter" key when finish
                    entering the hidden flag.
                </p>
            </div>

        </form>
</section>

<!-- Indicate footer (end part) section of the whole page. -->
<footer>
    <h2 class='ps-3'>Recent Data</h2>

    <!-- Boostrap Grid Table System. -->
    <div class="container-fluid">
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
        $sql = $conn->query("SELECT * FROM ModuleData WHERE moduleID = " . $challengeToLoad . " ORDER BY id DESC LIMIT 10");
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

<!-- Check if we should display the ESP32 modules data and give the end-users' point if they got the flag right. -->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEnteredFlag = sanitise_data($_POST['hiddenflag']);
    //    $challengeToLoad = $_GET["moduleID"];
    //    $flagList = $conn->query("SELECT HashedFlag, PointsValue, moduleID, challengeTitle, challengeText, PointsValue FROM Challenges WHERE moduleID = " . $challengeToLoad . "");
    //
    //    while ($flagData = $flagList->fetch()) {
//                if (password_verify($userEnteredFlag, $hashedFlag)) {
    if ($userEnteredFlag == $hashedFlag) {
        $user = $_SESSION["user_id"];
        $sql = "UPDATE Users SET Score = SCORE + '$pointsValue' WHERE ID='$user'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        //        $userInformation = $conn->query("SELECT Score FROM Users WHERE ID='$user'");
        //        $userData = $userInformation->fetch();
        //        $addedScore = $userData["Score"] += $pointsValue;
        //        $sql1 = "UPDATE Users SET Score=? WHERE Username=?";
        //        $stmt = $conn->prepare($sql1);
        //        $stmt->execute([$addedScore, $user]);

        $sql = "UPDATE RegisteredModules SET CurrentOutput = 'On' WHERE ID='$moduleID'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $_SESSION["flash_message"] = "<div class='bg-success'>Success!</div>";
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger'>Flag failed - Try again</div>";
    }
}
echo outputFooter();
?>
</body>
</html>