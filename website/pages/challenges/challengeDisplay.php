<?php
include "../../includes/template.php"; // gives $conn and (likely) set_flash(), sanitise_data(), authorisedAccess()
/** @var $conn */

// ---------- Safe redirect (works even if headers already sent) ----------
if (!function_exists('safe_redirect')) {
    function safe_redirect(string $url): void {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        }
        echo '<script>location.replace(' . json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ');</script>';
        exit;
    }
}

// ---------- Helpers (guarded so we don't redeclare template functions) ----------
if (!function_exists('sanitise_data')) {
    function sanitise_data($v) { return trim((string)$v); }
}
if (!function_exists('makeLinksClickable')) {
    function makeLinksClickable($text) {
        $pattern = '/(https?:\/\/[^\s<]+)/i';
        return preg_replace_callback($pattern, function($m) {
            $url = htmlspecialchars($m[0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
        }, $text);
    }
}
function flash_msg(string $type, string $msg): void {
    if (function_exists('set_flash')) { set_flash($type, $msg); return; }
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

// ---------- Session/Auth ----------
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (function_exists('authorisedAccess')) {
    if (!authorisedAccess(false, true, true)) { safe_redirect("../../index.php"); }
}

$userID = $_SESSION["user_id"] ?? null;
if (!$userID) { safe_redirect("../../index.php"); }

// ---------- Inputs ----------
$challengeToLoad = isset($_GET["challengeID"]) ? (int)$_GET["challengeID"] : 0;
if ($challengeToLoad <= 0) { safe_redirect("./challengesList.php"); }

// ---------- Load challenge ----------
$stmt = $conn->prepare("SELECT ID, challengeTitle, challengeText, pointsValue, flag, files FROM Challenges WHERE ID = ?");
$stmt->execute([$challengeToLoad]);
$ch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ch) {
    flash_msg('danger', 'Challenge not found.');
    safe_redirect("./challengesList.php");
}

$challengeID   = (int)$ch['ID'];
$title         = (string)$ch['challengeTitle'];
$challengeText = (string)$ch['challengeText'];
$pointsValue   = (int)$ch['pointsValue'];
$flag          = (string)$ch['flag'];
$files         = (string)$ch['files'];

// Project (for returning to the correct list filter)
$projStmt = $conn->prepare("SELECT project_id FROM ProjectChallenges WHERE challenge_id = ?");
$projStmt->execute([$challengeID]);
$projectID = $projStmt->fetchColumn();
$listUrl   = "./challengesList.php" . ($projectID ? ("?projectID=" . urlencode((string)$projectID)) : "");

// ---------- POST: check flag then go back to list ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hiddenflag"])) {
    $userFlag = sanitise_data($_POST["hiddenflag"]);

    if ($userFlag === $flag) {
        // Already solved?
        $chk = $conn->prepare("SELECT 1 FROM UserChallenges WHERE userID = ? AND challengeID = ?");
        $chk->execute([$userID, $challengeID]);
        if ($chk->fetch()) {
            flash_msg('warning', 'Flag Success! Challenge already completed, no points awarded');
            safe_redirect($listUrl);
        }

        // Record solve + award points
        $ins = $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)");
        $ins->execute([$userID, $challengeID]);

        $upd = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE ID = ?");
        $upd->execute([$pointsValue, $userID]);

        // Set moduleValue to 1 on successful flag submit
        $mv = $conn->prepare("UPDATE Challenges SET moduleValue = 1 WHERE ID = ?");
        $mv->execute([$challengeID]);

        flash_msg('success', 'Success!');
        safe_redirect($listUrl);
    } else {
        flash_msg('danger', 'Flag failed - try again');
        // Stay on this page to try again
        $self = strtok($_SERVER['REQUEST_URI'], '#');
        safe_redirect($self);
    }
}

// ---------- GET render ----------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Challenge Information</title>
    <style>
        .flag-input { background-color: white !important; color: black !important; }
    </style>
</head>
<body>

<header class="container text-center mt-4">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
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
                <td class="text-start">
                    <?= nl2br(makeLinksClickable(htmlspecialchars($challengeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))) ?>
                </td>
                <td>
                    <?php if (!empty($files)): ?>
                        <a href="<?= htmlspecialchars($files, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                           download class="btn btn-primary btn-sm">Download File</a>
                    <?php else: ?>
                        <span class="text-muted">No file required</span>
                    <?php endif; ?>
                </td>
                <td class="fw-bold"><?= htmlspecialchars((string)$pointsValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
            </tr>
            </tbody>
        </table>
    </div>

    <p class="text-success fw-bold text-center mt-3">Good luck and have fun!</p>
    <hr class="my-4 border-2 border-danger opacity-100">

    <!-- Flag Submission -->
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
          method="post" class="mt-3">
        <div class="form-floating mb-3">
            <input type="text"
                   class="form-control flag-input"
                   id="flag"
                   name="hiddenflag"
                   placeholder="CTF{Flag_Here}"
                   autocomplete="off">
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
            $md = $conn->prepare("SELECT DateTime, Data FROM ModuleData WHERE ModuleID = ?");
            $md->execute([$challengeID]);
            while ($row = $md->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row["DateTime"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
                echo '<td>' . makeLinksClickable(htmlspecialchars($row["Data"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</footer>

<script>
    // Keep tables matching dark/light mode if your template toggles classes
    function applyTableTheme() {
        const body = document.body;
        const tables = document.querySelectorAll('.theme-table');

        tables.forEach(table => {
            table.classList.remove('table-dark', 'table-light');
            table.classList.add(body.classList.contains('bg-dark') ? 'table-dark' : 'table-light');
        });

        if (body.classList.contains('bg-dark')) {
            body.classList.add('text-light'); body.classList.remove('text-dark');
        } else {
            body.classList.add('text-dark'); body.classList.remove('text-light');
        }
    }
    applyTableTheme();
    document.getElementById('modeToggle')?.addEventListener('click', () => setTimeout(applyTableTheme, 60));
</script>

</body>
</html>
