<?php
require('../../../includes/config.php');
/**@var $conn*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   /**
    echo "<pre>";
    print_r($_POST); // or use var_dump($_POST);
    echo "</pre>";
    exit;
    */
    $dChallengeID = $_POST['dChallengeID'] ?? null;
    $userID = $_POST['userID'] ?? null;

    if (!$dChallengeID || !$userID) {
        die("Error: Missing data for dChallengeID or userID.");
    }
    $current_date = date('Y-m-d H:i:s');
    echo $current_date;
    $dockerContainerInsert = $conn->query("INSERT INTO docker_containers (challengeID, userID, timeInitialised) VALUES ('$dChallengeID', '$userID', '$current_date')");
}
?>