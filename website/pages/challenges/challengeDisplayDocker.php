<?php
include "../../includes/template.php";
$challengeToLoad = $_GET["challengeID"] ?? null;

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
    exit;
}

if (!$challengeToLoad) {
    header("Location: challengesList.php");
    exit;
}

$userID = $_SESSION["user_id"];

/* ------------ FUNCTIONS ------------- */
function loadChallengeData() {
    global $conn, $challengeToLoad, $challengeID, $title, $challengeText, $pointsValue, $flag, $Image;

    $stmt = $conn->prepare("SELECT ID, challengeTitle, challengeText, pointsValue, flag, Image FROM Challenges WHERE ID = ?");
    $stmt->execute([$challengeToLoad]);
    if ($row = $stmt->fetch()) {
        $challengeID   = $row["ID"];
        $title         = $row["challengeTitle"];
        $challengeText = $row["challengeText"];
        $pointsValue   = $row["pointsValue"];
        $flag          = $row["flag"];
        $Image         = $row["Image"];
    } else {
        // Challenge not found
        echo "<div class='alert alert-danger text-center mt-4'>Challenge not found.</div>";
        exit;
    }
}

function getContainerInfo($userID, $challengeID) {
    global $conn;
    $containerQuery = $conn->prepare("SELECT timeInitialised, port FROM DockerContainers WHERE userID = ? AND challengeID = ?");
    $containerQuery->execute([$userID, $challengeID]);
    return $containerQuery->fetch();
}

function checkFlag() {
    global $conn, $challengeID, $flag, $pointsValue, $userID;
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $userEnteredFlag = sanitise_data($_POST['hiddenflag']);
        if ($userEnteredFlag === $flag) {
            $query = $conn->prepare("SELECT 1 FROM UserChallenges WHERE challengeID=? AND userID=?");
            $query->execute([$challengeID, $userID]);

            if ($query->fetch()) {
                $_SESSION["flash_message"] = "<div class='bg-warning text-center p-2'>Flag Success! Challenge already completed, no points awarded.</div>";
                header("Location: ./challengesList.php");
                exit;
            }

            // Insert into UserChallenges
            $insert = $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)");
            $insert->execute([$userID, $challengeID]);

            // Update user score
            $updateScore = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE ID = ?");
            $updateScore->execute([$pointsValue, $userID]);

            $_SESSION["flash_message"] = "<div class='bg-success text-center p-2'>Success!</div>";
            header("Location: ./challengesList.php");
            exit;
        } else {
            $_SESSION["flash_message"] = "<div class='bg-danger text-center p-2'>Flag failed - try again</div>";
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

loadChallengeData();
checkFlag();

$container = getContainerInfo($userID, $challengeID);
$port = $container['port'] ?? 'N/A';
$ipAddress = "10.177.202.196"; // Replace with dynamic IP if needed
$timeInitialised = $container['timeInitialised'] ?? null;
$deletionTime = $timeInitialised ? date('G:i', strtotime($timeInitialised) + 1200) : "Container not initialised";

?>

<title>Challenge Information</title>

<header class="container text-center mt-4">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($title) ?></h1>
</header>

<main class="container my-5">

    <!-- Challenge Details Table -->
    <div class="table-responsive my-4">
        <table class="table table-bordered table-hover text-center align-middle theme-table mb-0">
            <thead>
            <tr>
                <th style="width:15%">Challenge Image</th>
                <th style="width:20%">Challenge Name</th>
                <th style="width:50%">Description</th>
                <th style="width:10%">Points</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php if ($Image): ?>
                        <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= htmlspecialchars($Image) ?>" alt="Challenge Image" width="100" height="100">
                    <?php else: ?>
                        <span class="text-muted">No Image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($title) ?></td>
                <td class="text-start"><?= nl2br(htmlspecialchars($challengeText)) ?></td>
                <td class="fw-bold"><?= htmlspecialchars($pointsValue) ?></td>
            </tr>
            </tbody>
        </table>
    </div>

    <p class="text-success fw-bold text-center mt-3">Good luck and have fun!</p>
    <hr class="my-4 border-2 border-danger opacity-100">

    <!-- Flag Submission -->
    <form action="challengeDisplay.php?challengeID=<?= $challengeID ?>" method="post" class="mt-3">
        <div class="form-floating mb-3">
            <input type="text"
                   class="form-control flag-input"
                   id="flag"
                   name="hiddenflag"
                   placeholder="CTF{Flag_Here}">
            <p class="form-text text-start small">
                Press <b>Enter</b> when finished entering the flag.
            </p>
        </div>
    </form>

    <!-- Container Info Table -->
    <div class="table-responsive my-4">
        <table class="table table-bordered table-striped text-center align-middle theme-table mb-0">
            <thead>
            <tr>
                <th>Container Info</th>
                <th>Controls</th>
                <th>Shutdown Time</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?= $timeInitialised ? "IP: $ipAddress<br>Port: $port" : "Container not initialised" ?>
                </td>
                <td>
                    <button type="button" class="btn btn-success" onclick="startContainer(<?= $challengeID ?>, <?= $userID ?>)">Start Container</button>
                </td>
                <td>
                    <?= $deletionTime ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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

    // Dark/Light Mode Table Toggling
    function applyTableTheme() {
        const body = document.body;
        const tables = document.querySelectorAll('.theme-table');
        tables.forEach(table => {
            table.classList.remove('table-dark', 'table-light');
            if (body.classList.contains('bg-dark')) {
                table.classList.add('table-dark');
            } else {
                table.classList.add('table-light');
            }
        });
    }

    applyTableTheme();
    document.getElementById('modeToggle')?.addEventListener('click', () => {
        setTimeout(applyTableTheme, 50);
    });
</script>