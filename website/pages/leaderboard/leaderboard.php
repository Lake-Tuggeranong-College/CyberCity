<?php include "../../includes/template.php";
/** @var $conn */
$sec = 60;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(true, true, true)) {
    header("Location:../../index.php");
}

?>
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--<head>-->
<!--    <meta http-equiv="refresh" content="--><?php //echo $sec ?><!--;URL='--><?php //echo $page ?><!--'">-->
<!--    <title>Cyber City - Leaderboard</title>-->
<!--</head>-->
<!--<body>-->
<div class = "wideBox">
    <div class = "title" >
        <h1>Leaderboard</h1>
        <div class="table-responsive">
            <div class="container-fluid">
                <div class="row">
                    <div class="leaderboard" style="min-width: 20rem; max-width: 20rem"><strong>Username</strong></div>
                    <div class="leaderboard" style="min-width: 20rem; max-width: 20rem"><strong>Score</strong></div>
                </div>

<?php
$scoreList = $conn->query("SELECT Username, Score FROM Users WHERE AccessLevel=1 AND Enabled=1 ORDER BY Score DESC");

while ($scoreData = $scoreList->fetch()) {
    if ($scoreData[1] != 0) {
        echo "<div class='row'>";
        echo "<div class='leaderboard' style='min-width: 300px; max-width: 50%'>" . $scoreData[0] . "</div>";
        echo "<div class='leaderboard' style='min-width: 30px; max-width: 50%'>" . $scoreData[1] . "</div>";
        echo "</div>";


    }
}

?>
            </div>
        </div>
    </div>
</div>
<?php echo outputFooter(); ?>


