<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

?>
<title>Challenge Information</title><br>

<head>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>

            <th>Challenge Number</th>
            <th>Challenge Description</th>
            <th>Challenge Points</th>


        </thead>
</head>
        <body>
        <h1 class='text-primary'>Flag Claimer</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="col-md-12">
                <p>Enter the flag below to claim it and get points!</p>
                <p>Flag<input type="text" name="flag" class="form-control" required="required"></p>
            </div>
            <input type="submit" name="formSubmit" value="Claim">
        </form>
        </body>


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
?>

<?php
//if (isset($_POST['login'])) {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flag = sanitise_data($_POST['flag']);

    $flagList = $conn->query("SELECT HashedFlag,PointsValue FROM Challenges");

    while ($flagData = $flagList->fetch()) {
        if (password_verify($flag, $flagData[0])) {
            $username = $_SESSION["username"];
            $userInformation = $conn->query("SELECT Username, Score FROM Users WHERE Username='$username'");
            $userData = $userInformation->fetch();
            $addedScore = $userData[1] += $flagData[1];
            $sql = "UPDATE Users SET Score=? WHERE Username=?";
            // change to UPDATE
            $stmt = $conn->prepare($sql);
            $stmt->execute([$addedScore, $username]);
            echo "added points";
        } else {
            echo "Could not find flag :(";
        }
    }
}

?>

    </table>
</div>

<?php echo outputFooter(); ?>
</body>
</html>
