<?php
include "../../includes/template.php";

// ---- Resolve and validate challengeID from query ----
$challengeToLoad = isset($_GET["challengeID"]) ? (int) $_GET["challengeID"] : -1;

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
    exit;
}

// ---- Fetch challenge info ----
$sql = $conn->prepare("
    SELECT ID, moduleName, challengeTitle, challengeText, pointsValue, flag, dockerChallengeID, Image
    FROM Challenges
    WHERE ID = ?
");
$sql->execute([$challengeToLoad]);
$challenge = $sql->fetch(PDO::FETCH_ASSOC);

if (!$challenge) {
    echo "Challenge not found.";
    exit;
}

extract($challenge); // Creates $ID, $moduleName, $challengeTitle, etc.

$userID = $_SESSION["user_id"] ?? null;
if (!$userID) {
    header("Location:../../index.php");
    exit;
}

// ---- Fetch container info if running ----
$containerQuery = $conn->prepare("
    SELECT timeInitialised, port
    FROM DockerContainers
    WHERE userID = ? AND challengeID = ?
    LIMIT 1
");
$containerQuery->execute([$userID, $ID]);
$container = $containerQuery->fetch(PDO::FETCH_ASSOC);

$port            = $container['port'] ?? null;
$ipAddress       = "10.177.202.196"; // TODO: make dynamic if needed
$timeInitialised = $container['timeInitialised'] ?? null;
$deletionTime    = $timeInitialised
    ? date('G:i', strtotime($timeInitialised) + 20 * 60) // +20 minutes
    : "Container not initialised";

$isRunning = (bool) $timeInitialised;

// ---- Handle flag submission ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hiddenflag'])) {
    $userFlag = sanitise_data($_POST['hiddenflag']);

    if ($userFlag === $flag) {
        // already completed?
        $check = $conn->prepare("SELECT 1 FROM UserChallenges WHERE userID = ? AND challengeID = ?");
        $check->execute([$userID, $ID]);

        if ($check->rowCount() > 0) {
            $_SESSION["flash_message"] = "<div class='bg-warning p-2'>Flag Success! Already completed.</div>";
        } else {
            $ins = $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)");
            $ins->execute([$userID, $ID]);

            $upd = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE ID = ?");
            $upd->execute([$pointsValue, $userID]);

            $_SESSION["flash_message"] = "<div class='bg-success p-2'>Success!</div>";
        }
        header("Location:./challengesList.php");
        exit;
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger p-2'>Flag failed - try again</div>";
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?challengeID=' . (int)$ID);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Challenge Info</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- (Bootstrap assumed available via your template include) -->
    <style>
        .flag-input { width: 100%; max-width: 420px; }
        .btn-wide { min-width: 170px; }
    </style>
</head>
<body>
<header class="container-fluid d-flex align-items-center justify-content-center mt-3">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($challengeTitle) ?></h1>
</header>

<section class="container-fluid mt-3" style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border fw-bold">
            <div class="col-2">Image</div>
            <div class="col-2">Title</div>
            <div class="col-6">Description</div>
            <div class="col-2">Points</div>
        </div>
        <div class="row border border-top-0 align-items-center">
            <div class="col-2 py-2">
                <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= $Image ? htmlspecialchars($Image) : 'Image Not Found.jpg' ?>" width="100" height="100" alt="Challenge image">
            </div>
            <div class="col-2"><?= htmlspecialchars($challengeTitle) ?></div>
            <div class="col-6 text-start"><?= nl2br(htmlspecialchars($challengeText)) ?></div>
            <div class="col-2"><?= (int)$pointsValue ?></div>
        </div>

        <?php if (!empty($_SESSION["flash_message"])): ?>
            <div class="mt-3">
                <?= $_SESSION["flash_message"]; unset($_SESSION["flash_message"]); ?>
            </div>
        <?php endif; ?>

        <div class="pt-3 text-success fw-bold">Good luck and have fun!</div>

        <hr style="height: 4px; background-color: red;">

        <form method="post" class="d-inline-block">
            <input type="text" name="hiddenflag" class="form-control flag-input" placeholder="CTF{Flag_Here}" autocomplete="off">
            <p class="form-text text-start font-size-sm">Press Enter when you're done entering your flag.</p>
        </form>
    </div>
</section>

<section class="container-fluid" style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border fw-bold">
            <div class="col">Container Info</div>
            <div class="col">Controls</div>
            <div class="col">Shutdown Time</div>
        </div>
        <div class="row border border-top-0 align-items-center">
            <div class="col py-2" id="containerInfo">
                <?php if ($isRunning): ?>
                    IP: <?= htmlspecialchars($ipAddress) ?><br>
                    Port: <?= htmlspecialchars((string)$port) ?>
                <?php else: ?>
                    Container not initialised
                <?php endif; ?>
            </div>
            <div class="col py-2">
                <?php if ($isRunning): ?>
                    <button
                            id="toggleBtn"
                            class="btn btn-danger btn-wide"
                            data-state="running"
                            onclick="toggleContainer(<?= (int)$ID ?>, <?= (int)$userID ?>)">
                        Stop Container
                    </button>
                <?php else: ?>
                    <button
                            id="toggleBtn"
                            class="btn btn-success btn-wide"
                            data-state="stopped"
                            onclick="toggleContainer(<?= (int)$ID ?>, <?= (int)$userID ?>)">
                        Start Container
                    </button>
                <?php endif; ?>
            </div>
            <div class="col py-2" id="shutdownCell">
                <?= htmlspecialchars($deletionTime) ?>
            </div>
        </div>
        <div class="small text-muted mt-2">Note: Containers automatically stop 20 minutes after start.</div>
    </div>
</section>

<script>
    // Helper to disable/enable the toggle button
    function setBtnBusy(busy, label) {
        const btn = document.getElementById('toggleBtn');
        if (!btn) return;
        btn.disabled = !!busy;
        if (label) btn.textContent = label;
    }

    // Optimistically swap UI state (colour/label) before server confirms
    function setBtnState(state) {
        const btn = document.getElementById('toggleBtn');
        if (!btn) return;
        btn.dataset.state = state;
        if (state === 'running') {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-danger');
            btn.textContent = 'Stop Container';
        } else {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-success');
            btn.textContent = 'Start Container';
        }
    }

    function toggleContainer(challengeID, userID) {
        const btn = document.getElementById('toggleBtn');
        if (!btn) return;

        const currentState = btn.dataset.state; // 'running' | 'stopped'
        const isStarting = currentState === 'stopped';

        // Prevent double-clicks
        setBtnBusy(true, isStarting ? 'Starting…' : 'Stopping…');

        const url = isStarting
            ? '<?= BASE_URL ?>pages/challenges/docker/startContainer.php'
            : '<?= BASE_URL ?>pages/challenges/docker/stopContainer.php';

        // Optimistic label change
        setBtnState(isStarting ? 'running' : 'stopped');

        axios.post(url, {
            challengeID: challengeID,
            userID: userID
        }).then(res => {
            // Give the backend a moment to update DB/containers, then refresh
            setTimeout(() => {
                location.reload();
            }, 800);
        }).catch(err => {
            // Revert optimistic change on error
            setBtnState(currentState);
            setBtnBusy(false, currentState === 'stopped' ? 'Start Container' : 'Stop Container');
            console.error(err);
            alert('Action failed. Please try again.');
        });
    }
</script>
</body>
</html>
