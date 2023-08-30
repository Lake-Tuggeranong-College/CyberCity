<?php include "template.php";
/** @var $conn */
$sec = 5;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(true, true, true)) {
    header("Location:index.php");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'">
    <title>Cyber City - Leaderboard</title>
</head>
<body>
<h1 class='text-primary'>Leaderboard</h1>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Username</th>
            <th>Score</th>
        </thead>
</body>
</html>


<?php
$scoreList = $conn->query("SELECT Username, Score FROM Users WHERE AccessLevel=1 AND Enabled=1 ORDER BY Score DESC");

while ($scoreData = $scoreList->fetch()) {
    if ($scoreData[1] != 0) {
        echo "<tr>";
        echo "<td>" . $scoreData[0] . "</td>";
        echo "<td>" . $scoreData[1] . "</td>";
        echo "</tr>";


    }
}

?>


