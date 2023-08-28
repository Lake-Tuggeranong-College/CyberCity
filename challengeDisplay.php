<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>

<html>
<head>
    <title>Challenge Information</title><br>
</head>
<body>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>

            <th>Challenge Number</th>
            <th>Challenge Description</th>
            <th>Challenge Points</th>


        </thead>
</table>
</div>
</body>
</html>
<?php
if (isset($_GET["moduleID"])) {
    $challengeToLoad = $_GET["moduleID"];
} else {
   header("location:challengesList.php");
}

$sql = "SELECT moduleID, challengeTitle, challengeText, PointsValue FROM Challenges WHERE moduleID = " . $challengeToLoad . " ORDER BY ID DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $row_id = $row["moduleID"];
        $row_challengeTitle = $row["challengeTitle"];
        $row_challengeText = $row["challengeText"];
        $row_PointsValue = $row["PointsValue"];

        echo "<title> ID:" . $row_id . "</title>";
        echo "<tr>";
        echo "<td>" . $row_challengeTitle . "</td>";
        echo "<td>" . $row_challengeText . "  </td>";
        echo "<td>" . $row_PointsValue . "  </td>";
        echo "</tr>";

    }
    $result = null;
}
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
<!--<form action="--><?php //echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><!--" method="post" enctype="multipart/form-data">-->
<!--    <div class="col-md-12">-->
<!--        <p>Enter the flag below to claim it and get points!</p>-->
<!--        <p>Flag<input type="text" name="flag" class="form-control" required="required"></p>-->
<!--    </div>-->
<!-- <input type="submit" name="formSubmit" value="Claim">-->
<!--</form>-->
</body>
<div class="col-md-12">
<a href="/flagClaimer.php">Click here to claim the flag</a>
</div>
</html>





<?php echo outputFooter(); ?>

