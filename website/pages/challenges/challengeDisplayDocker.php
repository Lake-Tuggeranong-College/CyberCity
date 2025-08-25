<?php
include "../../includes/template.php";
$challengeToLoad = $_GET["challengeID"] ?? -1;

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
    exit;
}

// Fetch challenge info
$sql = $conn->prepare("SELECT ID, moduleName, challengeTitle, challengeText, pointsValue, flag, dockerChallengeID, Image FROM Challenges WHERE ID = ?");
$sql->execute([$challengeToLoad]);
$challenge = $sql->fetch();

if (!$challenge) {
    echo "Challenge not found.";
    exit;
}

extract($challenge); // Creates variables like $ID, $moduleName, $challengeTitle, etc.

$userID = $_SESSION["user_id"];

// Fetch container info if running
$containerQuery = $conn->prepare("SELECT timeInitialised, port FROM DockerContainers WHERE userID = ? AND challengeID = ?");
$containerQuery->execute([$userID, $ID]);
$container = $containerQuery->fetch();

$port = $container['port'] ?? 'N/A';
$ipAddress = "10.177.202.196"; // Replace with dynamic IP if needed
$timeInitialised = $container['timeInitialised'] ?? null;
$deletionTime = $timeInitialised ? date('G:i', strtotime($timeInitialised) + 1200) : "Container not initialised";

// Handle flag submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userFlag = sanitise_data($_POST['hiddenflag']);
    if ($userFlag === $flag) {
        $check = $conn->prepare("SELECT 1 FROM UserChallenges WHERE userID = ? AND challengeID = ?");
        $check->execute([$userID, $ID]);
        if ($check->rowCount() > 0) {
            $_SESSION["flash_message"] = "<div class='bg-warning'>Flag Success! Already completed.</div>";
        } else {
            $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)")->execute([$userID, $ID]);
            $conn->prepare("UPDATE Users SET Score = Score + ? WHERE ID = ?")->execute([$pointsValue, $userID]);
            $_SESSION["flash_message"] = "<div class='bg-success'>Success!</div>";
        }
        header("Location:./challengesList.php");
        exit;
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger'>Flag failed - try again</div>";
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Challenge Info</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<header class="container-fluid d-flex align-items-center justify-content-center">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($challengeTitle) ?></h1>
</header>

<section style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border">
            <div class="col-2">Image</div>
            <div class="col-2">Title</div>
            <div class="col-6">Description</div>
            <div class="col-2">Points</div>
        </div>
        <div class="row border border-top-0">
            <div class="col-2">
                <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= $Image ?: 'Image Not Found.jpg' ?>" width="100" height="100">
            </div>
            <div class="col-2"><?= htmlspecialchars($challengeTitle) ?></div>
            <div class="col-6"><?= nl2br(htmlspecialchars($challengeText)) ?></div>
            <div class="col-2"><?= $pointsValue ?></div>
        </div>
        <div class="pt-3 text-success fw-bold">Good luck and have fun!</div>

        <hr style="height: 4px; background-color: red;">

        <form method="post">
            <input type="text" name="hiddenflag" class="flag-input" placeholder="CTF{Flag_Here}">
            <p class="form-text text-start font-size-sm">Press Enter when you're done entering your flag.</p>
        </form>
    </div>
</section>

<section style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border">
            <div class="col">Container Info</div>
            <div class="col">Controls</div>
            <div class="col">Shutdown Time</div>
        </div>
        <div class="row border border-top-0">
            <div class="col">
                <?= $timeInitialised ? "IP: $ipAddress<br>Port: $port" : "Container not initialised" ?>
            </div>
            <div class="col">
                <button class="btn btn-success" onclick="startContainer(<?= $ID ?>, <?= $userID ?>)">Start Container</button>
            </div>
            <div class="col">
                <?= $deletionTime ?>
            </div>
        </div>
    </div>
</section>

<script>
    function startContainer(challengeID, userID) {
        axios.post('<?= BASE_URL ?>pages/challenges/docker/startContainer.php', {
            challengeID: challengeID,
            userID: userID
        }).then(res => {
            console.log('Container started:', res.data);
            setTimeout(() => location.reload(), 1000);
        }).catch(err => {
            alert("Failed to start container.");
            console.error(err);
        });
    }
</script>
</body>
</html>
