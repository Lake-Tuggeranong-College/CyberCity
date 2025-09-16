<?php
include "../../includes/template.php";

/* ---------- Safe redirect helper (avoids 'headers already sent') ---------- */
function safe_redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    }
    echo '<script>location.replace(' . json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ');</script>';
    exit;
}

/* ---------- HTTP POST helpers (server-side stop attempts) ---------- */
function http_post_json(string $url, array $payload, int $timeout = 4): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => $timeout,
    ]);
    $respBody = curl_exec($ch);
    $err      = curl_error($ch);
    $status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['ok' => $err === '' && $status >= 200 && $status < 300, 'status' => $status, 'error' => $err, 'body' => $respBody];
}

function http_post_form(string $url, array $payload, int $timeout = 4): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_TIMEOUT        => $timeout,
    ]);
    $respBody = curl_exec($ch);
    $err      = curl_error($ch);
    $status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['ok' => $err === '' && $status >= 200 && $status < 300, 'status' => $status, 'error' => $err, 'body' => $respBody];
}

// ---------------------------------------------------------
// Config: keep this in sync with the watcher
// ---------------------------------------------------------
$TIME_LIMIT_MINUTES = (int) (getenv('CYBER_DOCKER_TIME_LIMIT_MINUTES') ?: 10);

// ---------------------------------------------------------
// Auth & inputs
// ---------------------------------------------------------
if (!authorisedAccess(false, true, true)) {
    safe_redirect("../../index.php");
}

$challengeToLoad = isset($_GET["challengeID"]) ? (int) $_GET["challengeID"] : 0;
if ($challengeToLoad <= 0) {
    safe_redirect("./challengesList.php");
}

$userID = $_SESSION["user_id"] ?? null;
if (!$userID) {
    safe_redirect("../../index.php");
}

// ---------------------------------------------------------
// Fetch challenge
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT ID, challengeTitle, challengeText, pointsValue, flag, Image
    FROM Challenges
    WHERE ID = ?
");
$stmt->execute([$challengeToLoad]);
$challenge = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$challenge) {
    echo "<div class='alert alert-danger text-center mt-4'>Challenge not found.</div>";
    exit;
}

$challengeID   = (int)$challenge["ID"];
$title         = $challenge["challengeTitle"];
$challengeText = $challenge["challengeText"];
$pointsValue   = (int)$challenge["pointsValue"];
$flag          = $challenge["flag"];
$image         = $challenge["Image"];

// Build a self URL for redirects (stays on this page)
$selfUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?challengeID=' . $challengeID;

// ---------------------------------------------------------
// Handle flag submission
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hiddenflag"])) {
    $userFlag = sanitise_data($_POST["hiddenflag"]);

    // Best-effort: stop container via HTTP endpoint (JSON then FORM). Also schedule client fallback.
    $stopContainer = function() use ($challengeID, $userID) {
        if (!defined('BASE_URL')) return false;
        $stopUrl = rtrim(BASE_URL, '/') . '/pages/challenges/docker/stopContainer.php';
        $payload = ['challengeID' => $challengeID, 'userID' => $userID];

        // Try JSON (matches axios default)
        $resJson = http_post_json($stopUrl, $payload);
        if ($resJson['ok']) return true;

        // Fallback to form-encoded (in case PHP endpoint expects $_POST vars)
        $resForm = http_post_form($stopUrl, $payload);
        return $resForm['ok'];
    };

    // Always set client fallback to guarantee stop on reload
    $_SESSION['AUTO_STOP_CONTAINER'] = ['challengeID' => $challengeID, 'userID' => $userID];

    if ($userFlag === $flag) {
        // Already solved?
        $check = $conn->prepare("SELECT 1 FROM UserChallenges WHERE userID = ? AND challengeID = ?");
        $check->execute([$userID, $challengeID]);
        if ($check->fetch()) {
            $ok = $stopContainer();
            $_SESSION["flash_message"] = "<div class='bg-warning text-center p-2'>Flag Success! Challenge already completed, no points awarded." . ($ok ? " Container stopped." : " Stopping container…") . "</div>";
            safe_redirect($selfUrl);
        }

        // Record solve + add points
        $ins = $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES (?, ?)");
        $ins->execute([$userID, $challengeID]);

        $upd = $conn->prepare("UPDATE Users SET Score = Score + ? WHERE ID = ?");
        $upd->execute([$pointsValue, $userID]);

        $ok = $stopContainer();

        $_SESSION["flash_message"] = "<div class='bg-success text-center p-2'>Success!" . ($ok ? " Container stopped." : " Stopping container…") . "</div>";
        safe_redirect($selfUrl);
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger text-center p-2'>Flag failed - try again</div>";
        safe_redirect($selfUrl);
    }
}

// ---------------------------------------------------------
// Container state (per-user per-challenge)
// ---------------------------------------------------------
$containerStmt = $conn->prepare("
    SELECT timeInitialised, port
    FROM DockerContainers
    WHERE userID = ? AND challengeID = ?
    LIMIT 1
");
$containerStmt->execute([$userID, $challengeID]);
$container = $containerStmt->fetch(PDO::FETCH_ASSOC);

$ipAddress       = "10.177.202.196"; // TODO: make dynamic if needed
$timeInitialised = $container['timeInitialised'] ?? null;
$port            = $container['port'] ?? null;
$isRunning       = !empty($timeInitialised);

// Deletion time matches the watcher hard cap
$deletionTime = "Container not initialised";
if ($isRunning) {
    $t0 = strtotime($timeInitialised);
    if ($t0 !== false) {
        $deletionTime = date('G:i', $t0 + ($TIME_LIMIT_MINUTES * 60));
    }
}

// Dynamic SSH/SCP snippets (live port if running, placeholder otherwise)
$sshPort = $isRunning && $port ? (string)$port : "<PORT>";
$sshCmd  = "ssh -p {$sshPort} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null RoboCop@{$ipAddress}";
$scpCmd  = "scp -P {$sshPort} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null RoboCop@{$ipAddress}:/home/RoboCop/Alarm.png ./(Filename and type)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Challenge Info</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .flag-input { width: 100%; max-width: 420px; }
        .btn-wide   { min-width: 170px; }

        /* Make code panels follow Bootstrap theme vars */
        pre.bg-body-tertiary {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        pre.bg-body-tertiary code { color: inherit; }

        /* ---- Navbar override: in DARK mode make navbar white with black text ---- */
        [data-bs-theme="dark"] .navbar {
            background-color: #fff !important;
            color: #000;
        }
        [data-bs-theme="dark"] .navbar .navbar-brand,
        [data-bs-theme="dark"] .navbar .nav-link,
        [data-bs-theme="dark"] .navbar .navbar-text {
            color: #000 !important;
        }
        [data-bs-theme="dark"] .navbar .nav-link:hover,
        [data-bs-theme="dark"] .navbar .nav-link:focus {
            color: #000 !important; opacity: .75;
        }
        [data-bs-theme="dark"] .navbar .dropdown-menu {
            --bs-dropdown-bg: #fff;
            --bs-dropdown-color: #000;
            --bs-dropdown-link-color: #000;
            --bs-dropdown-link-hover-bg: #f1f1f1;
            --bs-dropdown-link-hover-color: #000;
            --bs-dropdown-link-active-bg: #e9ecef;
            --bs-dropdown-link-active-color: #000;
            --bs-dropdown-border-color: #dee2e6;
        }
        [data-bs-theme="dark"] .navbar .navbar-toggler { border-color: #000; }
        [data-bs-theme="dark"] .navbar .navbar-toggler-icon {
            filter: invert(1) grayscale(100%) brightness(0);
        }
    </style>
</head>
<body>
<header class="container-fluid d-flex align-items-center justify-content-center mt-3">
    <h1 class="text-uppercase">Challenge - <?= htmlspecialchars($title) ?></h1>
</header>

<section class="container my-4">
    <?php if (!empty($_SESSION["flash_message"])): ?>
        <div class="mt-2">
            <?= $_SESSION["flash_message"]; unset($_SESSION["flash_message"]); ?>
        </div>
    <?php endif; ?>

    <!-- Challenge details -->
    <div class="table-responsive my-4">
        <table class="table table-bordered table-hover text-center align-middle theme-table mb-0">
            <thead>
            <tr>
                <th style="width:15%">Image</</th>
                <th style="width:20%">Title</th>
                <th style="width:50%">Description</th>
                <th style="width:10%">Points</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php if ($image): ?>
                        <img src="<?= BASE_URL ?>assets/img/challengeImages/<?= htmlspecialchars($image) ?>" alt="Challenge Image" width="100" height="100">
                    <?php else: ?>
                        <span class="text-muted">No Image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($title) ?></td>
                <td class="text-start"><?= nl2br(htmlspecialchars($challengeText)) ?></td>
                <td class="fw-bold"><?= (int)$pointsValue ?></td>
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

    <!-- Container controls -->
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
                <td id="containerInfo">
                    <?=
                    $isRunning
                        ? "IP: " . htmlspecialchars($ipAddress) . "<br>Port: " . htmlspecialchars((string)$port)
                        : "Container not initialised"
                    ?>
                </td>
                <td>
                    <!-- Stack buttons cleanly; works in light/dark mode -->
                    <div class="d-grid gap-2">
                        <?php if ($isRunning): ?>
                            <button
                                    id="toggleBtn"
                                    class="btn btn-danger btn-wide"
                                    data-state="running"
                                    onclick="toggleContainer(<?= (int)$challengeID ?>, <?= (int)$userID ?>)">
                                Stop Container
                            </button>
                        <?php else: ?>
                            <button
                                    id="toggleBtn"
                                    class="btn btn-success btn-wide"
                                    data-state="stopped"
                                    onclick="toggleContainer(<?= (int)$challengeID ?>, <?= (int)$userID ?>)">
                                Start Container
                            </button>
                        <?php endif; ?>

                        <button type="button"
                                class="btn btn-outline-secondary btn-wide"
                                data-bs-toggle="modal"
                                data-bs-target="#sshHelpModal">
                            SSH Connection Help
                        </button>
                    </div>
                </td>
                <td id="shutdownCell">
                    <?= htmlspecialchars($deletionTime) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="small text-body-secondary mt-2">
            Note: Containers automatically stop <?= (int)$TIME_LIMIT_MINUTES ?> minutes after start.
        </div>
    </div>
</section>

<!-- SSH Help Modal -->
<div class="modal fade" id="sshHelpModal" tabindex="-1" aria-labelledby="sshHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sshHelpModalLabel">SSH: Fix Host Key Prompts (Lab Use Only)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Quick commands for this challenge -->
                <h6 class="mb-2">Quick commands</h6>
                <div class="mb-3">
                    <div class="small text-body-secondary">SSH:</div>
                    <pre class="border rounded p-3 bg-body-tertiary"><code><?= htmlspecialchars($sshCmd) ?></code></pre>
                    <div class="small text-body-secondary">SCP (download example):</div>
                    <pre class="border rounded p-3 bg-body-tertiary"><code><?= htmlspecialchars($scpCmd) ?></code></pre>
                    <?php if (!$isRunning): ?>
                        <div class="small text-body-secondary">
                            Container is not running yet — the command shows <code>&lt;PORT&gt;</code>. Start the container to see the live port.
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="my-4">

                <ol class="mb-3">
                    <li><strong>Open your terminal.</strong></li>

                    <li class="mt-2">
                        <strong>Edit or create the SSH config file:</strong>
                        <pre class="border rounded p-3 bg-body-tertiary"><code>nano ~/.ssh/config</code></pre>
                        <div class="small text-body-secondary">If the file does not exist, this will open a blank file.</div>
                    </li>

                    <li class="mt-2">
                        <strong>Add this to the file:</strong>
                        <pre class="border rounded p-3 bg-body-tertiary"><code>Host *
    StrictHostKeyChecking no
    UserKnownHostsFile=/dev/null</code></pre>

                        <ul class="small mb-0">
                            <li><code>Host *</code> applies to all hosts.</li>
                            <li><code>StrictHostKeyChecking no</code> automatically accepts new host keys.</li>
                            <li><code>UserKnownHostsFile=/dev/null</code> prevents writing to your <code>known_hosts</code> file.</li>
                        </ul>
                    </li>

                    <li class="mt-2">
                        <strong>Save and exit</strong> in nano: press <code>CTRL+O</code>, then <code>ENTER</code>. Press <code>CTRL+X</code> to close.
                    </li>

                    <li class="mt-2">
                        <strong>Set the correct permissions:</strong>
                        <pre class="border rounded p-3 bg-body-tertiary"><code>chmod 600 ~/.ssh/config</code></pre>
                    </li>
                </ol>

                <div class="alert alert-warning small mb-0">
                    Only use this in a controlled lab. Disabling host key checks is not safe on production networks.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Sync Bootstrap color mode with the page's body classes (so navbar + modal follow dark mode)
    function syncBootstrapThemeFromBody() {
        const theme = document.body.classList.contains('bg-dark') ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    }

    // Disable/enable button + label
    function setBtnBusy(busy, label) {
        const btn = document.getElementById('toggleBtn');
        if (!btn) return;
        btn.disabled = !!busy;
        if (label) btn.textContent = label;
    }

    // Optimistic swap of button state
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

    // Table theme that reads the canonical Bootstrap theme attribute
    function applyTableTheme() {
        const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const tables = document.querySelectorAll('.theme-table');
        tables.forEach(table => {
            table.classList.remove('table-dark', 'table-light');
            table.classList.add(theme === 'dark' ? 'table-dark' : 'table-light');
        });

        // Optional: keep body text color in sync
        if (theme === 'dark') {
            document.body.classList.add('text-light');
            document.body.classList.remove('text-dark');
        } else {
            document.body.classList.add('text-dark');
            document.body.classList.remove('text-light');
        }
    }

    // Initial calls after DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        syncBootstrapThemeFromBody();
        applyTableTheme();

        // ---- Client-side fallback: auto-stop container once after redirect ----
        <?php
        if (!empty($_SESSION['AUTO_STOP_CONTAINER']) && $isRunning) {
            $auto = $_SESSION['AUTO_STOP_CONTAINER'];
            // Clear the flag immediately so it only runs once
            unset($_SESSION['AUTO_STOP_CONTAINER']);
            $cid = (int)$auto['challengeID'];
            $uid = (int)$auto['userID'];
            echo "setTimeout(() => toggleContainer($cid, $uid), 250);\n";
        }
        ?>
    });

    // Re-apply on theme toggle button (from template.php)
    document.getElementById('modeToggle')?.addEventListener('click', () => {
        // Wait a tick for template’s toggle to flip body classes
        setTimeout(() => {
            syncBootstrapThemeFromBody();
            applyTableTheme();
        }, 60);
    });

    function toggleContainer(challengeID, userID) {
        const btn = document.getElementById('toggleBtn');
        if (!btn) return;

        const currentState = btn.dataset.state; // 'running' | 'stopped'
        const isStarting = currentState === 'stopped';
        const url = isStarting
            ? '<?= BASE_URL ?>pages/challenges/docker/startContainer.php'
            : '<?= BASE_URL ?>pages/challenges/docker/stopContainer.php';

        // Prevent double-clicks + optimistic UI
        setBtnBusy(true, isStarting ? 'Starting…' : 'Stopping…');
        setBtnState(isStarting ? 'running' : 'stopped');

        axios.post(url, {
            challengeID: challengeID,
            userID: userID
        }).then(() => {
            // allow DB/binlog to settle, then sync UI
            setTimeout(() => location.reload(), 800);
        }).catch(err => {
            // revert on error
            setBtnState(currentState);
            setBtnBusy(false, currentState === 'stopped' ? 'Start Container' : 'Stop Container');
            console.error(err);
            alert('Action failed. Please try again.');
        });
    }
</script>
</body>
</html>
