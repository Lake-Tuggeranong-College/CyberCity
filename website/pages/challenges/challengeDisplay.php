<?php
include "../../includes/template.php";
/** @var $conn */

$challengeToLoad = $_GET["challengeID"] ?? null;
if (!$challengeToLoad) {
    header("location:challengesList.php");
    exit;
}

$challengeID = $title = $challengeText = $pointsValue = $flag = $projectID = $files= null;

/* ------------ FUNCTIONS ------------- */

// Function to convert URLs in text to clickable links
function makeLinksClickable($text) {
    // Regex to find URLs (http, https)
    $pattern = '/(https?:\/\/[^\s]+)/i';
    // Replace URLs with anchor tags
    return preg_replace_callback($pattern, function($matches) {
        $url = htmlspecialchars($matches[0]);
        return "<a href=\"$url\" target=\"_blank\" rel=\"noopener noreferrer\">$url</a>";
    }, $text);
}

function loadChallengeData() {
    global $conn, $challengeToLoad, $challengeID, $title, $challengeText, $pointsValue, $flag, $projectID, $files;

    $stmt = $conn->prepare("SELECT ID, challengeTitle, challengeText, pointsValue, flag, files FROM Challenges WHERE ID = ?");
    $stmt->execute([$challengeToLoad]);
    if ($row = $stmt->fetch()) {
        $challengeID   = $row["ID"];
        $title         = $row["challengeTitle"];
        $challengeText = $row["challengeText"];
        $pointsValue   = $row["pointsValue"];
        $flag          = $row["flag"];
        $files         = $row["files"];
    }

    // Project ID
    $projectStmt = $conn->prepare("SELECT project_id FROM ProjectChallenges WHERE challenge_id = ?");
    $projectStmt->execute([$challengeID]);
    $result = $projectStmt->fetch(PDO::FETCH_ASSOC);
    $projectID = $result["project_id"] ?? null;
}

function checkFlag() {
    global $conn, $challengeID, $flag, $projectID, $pointsValue;
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $userEnteredFlag = sanitise_data($_POST['hiddenflag']);
        if ($userEnteredFlag === $flag) {
            $user = $_SESSION["user_id"];
            $query = $conn->prepare("SELECT 1 FROM UserChallenges WHERE challengeID=? AND userID=?");
            $query->execute([$challengeID, $user]);

            if ($query->fetch()) {
                set_flash('warning', 'Flag Success! Challenge already completed, no points awarded');
                header("Location:./challengesList.php");
                exit;
            }

            // Insert into UserChallenges
            $insert = $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)");
            $insert->execute([$user, $challengeID]);

            // Update user score
            $scoreStmt = $conn->prepare("SELECT Score FROM Users WHERE ID=?");
            $scoreStmt->execute([$user]);
            $userScore = $scoreStmt->fetchColumn();
            $newScore = $userScore + $pointsValue;

            $updateScore = $conn->prepare("UPDATE Users SET Score=? WHERE ID=?");
            $updateScore->execute([$newScore, $user]);

            // Increment module value

            $conn->exec("UPDATE Challenges SET moduleValue = 1 WHERE ID=$challengeID");
            shell_exec('./CyberCity/website/assets/30 sec timer.sh');

            set_flash('success', 'Success!');
            header("Location:./challengesList.php?projectID=$projectID");
            exit;
        } else {
            set_flash('danger', 'Flag failed - try again');
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

loadChallengeData();
?>

<title>Challenge Information</title>

<style>
    /* Keep the flag input textbox color consistent regardless of theme */
    .flag-input {
        background-color: white !important;
        color: black !important;
    }
</style>

<header class="container text-center mt-4">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($title) ?></h1>
</header>

<main class="container my-5">

    <!-- Challenge Details Table -->
    <div class="table-responsive my-4">
        <table class="table table-bordered table-hover text-center align-middle theme-table mb-0">
            <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th style="width:20%">Files</th>
                <th style="width:10%">Points</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="text-start"><?= nl2br(makeLinksClickable(htmlspecialchars($challengeText))) ?></td>
                <td>
                    <?php if (!empty($files)): ?>
                        <a href="<?= htmlspecialchars($files) ?>" download class="btn btn-primary btn-sm">
                            Download File
                        </a>
                    <?php else: ?>
                        <span class="text-muted">No file required</span>
                    <?php endif; ?>
                </td>
                <td class="fw-bold"><?= htmlspecialchars($pointsValue) ?></td>
            </tr>
            </tbody>
        </table>
    </div>

    <p class="text-success fw-bold text-center mt-3">Good luck and have fun!</p>
    <hr class="my-4 border-2 border-danger opacity-100">

    <!-- Flag Submission -->
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?challengeID=<?= $challengeID ?>" method="post" class="mt-3">
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

</main>

<footer class="container my-5">
    <h2 class="ps-1">Recent Data</h2>
    <div class="table-responsive my-4">
        <table class="table table-bordered table-striped text-center align-middle theme-table mb-0">
            <thead>
            <tr>
                <th style="width:30%">Date & Time</th>
                <th style="width:70%">Data</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $sql = $conn->query("SELECT * FROM ModuleData WHERE ModuleID=" . $challengeID);
            while ($row = $sql->fetch()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row["DateTime"]) . '</td>';
                echo '<td>' . makeLinksClickable(htmlspecialchars($row["Data"])) . '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</footer>

<?php checkFlag(); ?>

<!-- Dark/Light Mode Table Toggling -->
<script>
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

        if (body.classList.contains('bg-dark')) {
            body.classList.add('text-light');
            body.classList.remove('text-dark');
        } else {
            body.classList.add('text-dark');
            body.classList.remove('text-light');
        }
    }

    // Initial call
    applyTableTheme();

    // Re-apply on toggle button click
    document.getElementById('modeToggle')?.addEventListener('click', () => {
        setTimeout(applyTableTheme, 50);
    });
</script>