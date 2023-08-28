<?php include "template.php";
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

$title = $result["challengeTitle"];
$challengeText = $result["challengeText"];
$pointsValue = $result["PointsValue"];
$hashedFlag = $result["HashedFlag"];

?>
<title>Challenge Information</title>
<h1>Challenge - <?= $title ?></h1>
<div class="container-fluid">
    <div class="row">
        <div class="col-1">Challenge Number</div>
        <div class="col-10">Challenge Description</div>
        <div class="col-1">Challenge Points</div>
    </div>
    <div class="row">
        <div class="col-1"><?=$title?></div>
        <div class="col-10"><?=$challengeText?></div>
        <div class="col-1"><?=$pointsValue?></div>
    </div>

</div>


</body>
</html>

<?php
//if ($_SERVER["REQUEST_METHOD"] == "POST") {
//    $flag = sanitise_data($_POST['flag']);
//    $challengeToLoad = $_GET["moduleID"];
//    $flagList = $conn->query("SELECT HashedFlag, PointsValue, moduleID, challengeTitle, challengeText, PointsValue FROM Challenges WHERE moduleID = " . $challengeToLoad . "");
//
//    while ($flagData = $flagList->fetch()) {
//        if (password_verify($flag, $flagData[0])) {
//            $username = $_SESSION["username"];
//
//            $userInformation = $conn->query("SELECT Username, Score FROM Users WHERE Username='$username'");
//            $userData = $userInformation->fetch();
//            $addedScore = $userData[1] += $flagData[1];
//            $sql1 = "UPDATE Users SET Score=? WHERE Username=?";
//            // change to UPDATE
//            $stmt = $conn->prepare($sql1);
//            $stmt->execute([$addedScore, $username]);
//            echo "added points";
//        } else {
//            echo "Could not find flag :(";
//        }
//    }
//}
?>

<html>
<body>
<!--<h1 class='text-primary'>Flag Claimer</h1>-->
<!--<form action="-->
<?php //echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><!--" method="post" enctype="multipart/form-data">-->
<!--    <div class="col-md-12">-->
<!--        <p>Enter the flag below to claim it and get points!</p>-->
<!--        <p>Flag<input type="text" name="flag" class="form-control" required="required"></p>-->
<!--    </div>-->
<!-- <input type="submit" name="formSubmit" value="Claim">-->
<!--</form>-->
</body>
<div class="col-md-12">
    <a href="flagClaimer.php">Click here to claim the flag</a>
</div>
</html>


<?php echo outputFooter(); ?>

