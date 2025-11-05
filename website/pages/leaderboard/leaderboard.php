<?php

$sec = 30;
$page = $_SERVER['PHP_SELF'];

header("Refresh:$sec; url=$page");
include_once "../../includes/template.php";



if (!authorisedAccess(true, true, true)) {
    header("Location:../../index.php");
    exit;
}

// TODO: create a new value in the database that store user's avatar
$query = "SELECT ID, Username, Score FROM Users WHERE Enabled=1 and AccessLevel=1 ORDER BY Score DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$userScore = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="leaderboard-container">
    <div class="d-flex justify-content-center align-items-end top-three-ranking">
        <?php
            // Top 3 users will be display a bit more special than the others
            $topThreeUserRankingScore = array_slice($userScore, 0, 3);
            $topThreeUserPosition = ['first-place', 'second-place', 'third-place'];
        ?>

        <!-- Dynamic display the top 3 users on leaderboard -->
        <?php foreach ($topThreeUserRankingScore as $arrayIndex => $userData): ?>
            <div class="leaderboard-item <?= $topThreeUserPosition[$arrayIndex] ?>">
                <div class="user-profile" style="background-image: url();"></div>
                <div class="user-name"><?= htmlspecialchars($userData['Username']) ?></div>
                <div class="user-score"><?= htmlspecialchars($userData['Score']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="leaderboard-list">
        <?php
            // Max 10 users can be display on the leaderboard. 3 have been displayed in the above code so this one will display the rest 7
            $userRankingScore = array_slice($userScore, 3, 7);
        ?>

        <!-- Dynamic display the rest 7 on leaderboard -->
        <?php foreach ($userRankingScore as $arrayIndex => $userData): ?>
            <div class="leaderboard-item">
                <div class="user-position"><?= $arrayIndex + 4?></div>
                <div class="user-profile" style="background-image: url();"></div>
                <div class="user-name"><?= htmlspecialchars($userData['Username']) ?></div>
                <div class="user-score"><?= htmlspecialchars($userData['Score']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

