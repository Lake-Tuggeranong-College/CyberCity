<?php include "template.php";
/** @var $conn */
$sec = 60;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(true, true, true)) {
    header("Location:index.php");
}

?>
<!--<!DOCTYPE html>-->
<!--<html>-->
<!--<head>-->
<!--    <meta http-equiv="refresh" content="--><?php //echo $sec ?><!--;URL='--><?php //echo $page ?><!--'">-->
<!--    <title>Cyber City - Leaderboard</title>-->
<!--</head>-->
<!--<body>-->
<h1>Leaderboard</h1>
<div class="table-responsive">
    <!--<table class="table table-bordered">
        <thead>
        <tr>
            <th>Username</th>
            <th>Score</th>
        </thead>-->

    <div class="container-fluid">
        <div class="row">
            <div class="leaderbord" style="min-width: 300px; max-width: 50%"><strong>Username</strong></div>
            <div class="leaderbord" style="min-width: 30px; max-width: 50%"><strong>Score</strong></div>
        </div>

</body>
</html>


<?php
$scoreList = $conn->query("SELECT Username, Score FROM Users WHERE AccessLevel=1 AND Enabled=1 ORDER BY Score DESC");

while ($scoreData = $scoreList->fetch()) {
    if ($scoreData[1] != 0) {
        echo "<div class='row'>";
        echo "<div class='leaderbord' style='min-width: 300px; max-width: 50%'>" . $scoreData[0] . "</div>";
        echo "<div class='leaderbord' style='min-width: 30px; max-width: 50%'>" . $scoreData[1] . "</div>";
        echo "</div>";


    }
}

?>
</div>
<?php echo outputFooter(); ?>


