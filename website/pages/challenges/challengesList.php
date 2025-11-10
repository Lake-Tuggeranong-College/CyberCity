<?php
// Prevent "headers already sent" issues if template.php echoes anything
ob_start();

require_once "../../includes/template.php";
/** @var PDO $conn */

// Authorisation check (do this before we output anything ourselves)
if (!authorisedAccess(false, true, true)) {
    header("Location: ../../index.php");
    exit;
}

// Validate projectID early
$projectID = filter_input(INPUT_GET, 'projectID', FILTER_VALIDATE_INT);
if (!$projectID) {
    header("Location: ../../index.php");
    exit;
}

// Current user (needed to mark completed)
$userID = $_SESSION['user_id'] ?? null;

// Helper for safe HTML
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Render a single challenge card (always shows a card, even without image).
 * @param array $challengeData Row from Challenges/* joins
 * @param bool  $isCompleted   Whether this user has completed it
 */
function createChallengeCard(array $challengeData, bool $isCompleted): void
{
    $challengeID       = (int)$challengeData['ID'];
    $challengeTitle    = $challengeData['challengeTitle'] ?? 'Untitled Challenge';
    $pointsValue       = isset($challengeData['pointsValue']) ? (int)$challengeData['pointsValue'] : 0;
    $imageFileName     = trim((string)($challengeData['Image'] ?? ''));
    $dockerChallengeId = $challengeData['dockerChallengeID'] ?? null;

    // Build target link: docker variant if non-empty
    $href = ($dockerChallengeId !== null && $dockerChallengeId !== '' && $dockerChallengeId !== 0)
        ? "challengeDisplayDocker.php?challengeID={$challengeID}"
        : "challengeDisplay.php?challengeID={$challengeID}";

    // Pick image (fallback if missing)
    $imgSrc = $imageFileName !== ''
        ? BASE_URL . "assets/img/challengeImages/" . rawurlencode($imageFileName)
        : BASE_URL . "assets/img/challengeImages/Image%20Not%20Found.jpg";
    ?>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card card-theme h-100 shadow-sm">
            <!-- Image area (full image shown, centered; no cropping) -->
            <div class="position-relative card-img-top d-flex align-items-center justify-content-center img-letterbox" style="height:200px; overflow:hidden;">
                <?php if ($isCompleted): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge rounded-pill bg-success d-inline-flex align-items-center gap-1" style="box-shadow:0 0 0 1px rgba(0,0,0,.15);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>
                        Completed
                    </span>
                <?php endif; ?>
                <img
                        src="<?= e($imgSrc) ?>"
                        alt="<?= e($challengeTitle) ?>"
                        class="img-fluid"
                        style="max-height:100%; max-width:100%; object-fit:contain;"
                        loading="lazy"
                        decoding="async"
                >
            </div>

            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-2 text-truncate" title="<?= e($challengeTitle) ?>"><?= e($challengeTitle) ?></h5>
                <p class="card-text text-muted mb-3">Points: <?= $pointsValue ?></p>

                <?php if ($isCompleted): ?>
                    <a href="<?= e($href) ?>" class="btn btn-outline-success mt-auto">View Challenge</a>
                <?php else: ?>
                    <a href="<?= e($href) ?>" class="btn btn-warning mt-auto">Start Challenge</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Fetch and render challenges grouped by category.
 */
function displayResultsByCategory(PDO $conn, int $projectID, ?int $userID): void
{
    // Fetch all challenges for the project (with category)
    $sql = "
        SELECT cat.CategoryName, ch.*
        FROM Category AS cat
        JOIN Challenges AS ch        ON cat.id = ch.categoryID
        JOIN ProjectChallenges AS pc ON ch.id = pc.challenge_id
        JOIN Projects AS p           ON pc.project_id = p.project_id
        WHERE p.project_id = :project_id
        ORDER BY cat.CategoryName, ch.challengeTitle;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':project_id', $projectID, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "<p class='text-muted'>No challenges found for this project yet.</p>";
        return;
    }

    // Build a set of challengeIDs that the current user has completed in this project
    $completed = [];
    if ($userID) {
        $csql = "
            SELECT uc.challengeID
            FROM UserChallenges uc
            JOIN ProjectChallenges pc ON pc.challenge_id = uc.challengeID
            WHERE uc.userID = :uid AND pc.project_id = :pid
        ";
        $cstmt = $conn->prepare($csql);
        $cstmt->bindValue(':uid', $userID, PDO::PARAM_INT);
        $cstmt->bindValue(':pid', $projectID, PDO::PARAM_INT);
        $cstmt->execute();
        $completedIDs = $cstmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($completedIDs) {
            foreach ($completedIDs as $cid) {
                $completed[(int)$cid] = true;
            }
        }
    }

    $currentCategory = null;
    $openRow = false;

    foreach ($rows as $row) {
        if ($currentCategory !== $row['CategoryName']) {
            if ($openRow) {
                echo "</div>"; // close previous .row
                $openRow = false;
            }
            $currentCategory = $row['CategoryName'];
            echo "<h2 class='mt-4 mb-3'>" . e($currentCategory) . "</h2>";
            echo "<div class='row'>";
            $openRow = true;
        }

        $isCompleted = isset($completed[(int)$row['ID']]);
        createChallengeCard($row, $isCompleted);
    }

    if ($openRow) {
        echo "</div>";
    }
}
?>

    <!-- Theme-aware styles for letterbox + cards + music control -->
    <style>
        /* Letterbox behind images */
        .img-letterbox{ background-color: var(--bs-tertiary-bg, #f8f9fa); }
        body.bg-dark .img-letterbox{ background-color: #1f2330; }

        /* Card theming that follows light/dark mode */
        .card-theme{
            border-radius:12px; overflow:hidden;
            background-color: var(--bs-card-bg, #ffffff);
            border:1px solid var(--bs-border-color, #dee2e6);
            transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        [data-bs-theme="dark"] .card-theme{
            --bs-card-bg: #0f1422;
            --bs-border-color: #2b3243;
            background-color: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        body.bg-dark .card-theme{
            background-color: #0f1422;
            border-color: #2b3243;
        }
        [data-bs-theme="dark"] .card-theme .card-title,
        body.bg-dark .card-theme .card-title{ color:#e6e9ef; }
        [data-bs-theme="dark"] .card-theme .text-muted,
        body.bg-dark .card-theme .text-muted{ color:#9aa3b2 !important; }

        /* ===== Background music controller (same pattern as other pages) ===== */
        .bgm{
            position: fixed; right: 18px; bottom: 18px; z-index: 1000;
            display: flex; align-items: center; gap: 10px;
            border-radius: 14px;
            border: 1px solid var(--bs-border-color, #dee2e6);
            background: var(--bs-card-bg, #ffffff);
            box-shadow: 0 18px 50px rgba(0,0,0,.12);
            padding: 10px 12px;
            backdrop-filter: blur(8px);
            color: var(--bs-body-color, #0f172a);
        }
        body.bg-dark .bgm{
            border-color: #2b3243;
            background: rgba(17, 24, 39, 0.92);
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
            color: #e6e9ef;
        }
        .bgm .btn{
            display:inline-flex; align-items:center; justify-content:center;
            width: 36px; height: 36px; border-radius: 10px;
            border: 1px solid var(--bs-border-color, #dee2e6);
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
            color: inherit; cursor: pointer; user-select:none; font-weight: 800;
        }
        .bgm .btn:active{ transform: translateY(1px); }
        .bgm .status{ font-size:.85rem; opacity:.85; min-width: 90px; }
        .bgm input[type="range"]{
            width: 120px; height: 6px; border-radius: 999px; outline:none;
            background: linear-gradient(90deg, var(--bs-primary, #0d6efd), var(--bs-success, #198754));
            appearance: none; -webkit-appearance: none; border: none;
        }
        .bgm input[type="range"]::-webkit-slider-thumb{
            -webkit-appearance: none; appearance: none; width:14px; height:14px; border-radius:50%;
            background: #fff; border: 2px solid rgba(0,0,0,.25);
        }
        @media (max-width: 560px){
            .bgm .status{ display:none; }
            .bgm input[type="range"]{ width: 90px; }
        }
    </style>

    <link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/moduleList.css">

    <h1>Challenges</h1>

    <div class="container-fluid">
        <?php displayResultsByCategory($conn, $projectID, $userID); ?>
    </div>

    <!-- ===== Background Music (toggle remembers user’s choice) ===== -->
    <audio id="bgmAudio" preload="auto" loop playsinline>
        <source src="<?= rtrim(BASE_URL, '/'); ?>/assets/Audio/cyber-challenges.mp3" type="audio/mpeg">
        <!-- optional ogg: <source src="<?= rtrim(BASE_URL, '/'); ?>/assets/audio/cyber-challenges.ogg" type="audio/ogg"> -->
    </audio>

    <div class="bgm" role="group" aria-label="Background music controls">
        <button class="btn" id="bgmToggle" aria-pressed="false" title="Play/Pause">▶</button>
        <div class="status" id="bgmStatus">Muted</div>
        <input type="range" id="bgmVol" min="0" max="1" step="0.01" value="0.4" aria-label="Volume">
    </div>

    <script>
        (function(){
            const audio   = document.getElementById('bgmAudio');
            const toggle  = document.getElementById('bgmToggle');
            const status  = document.getElementById('bgmStatus');
            const vol     = document.getElementById('bgmVol');

            // Restore saved prefs (shared across pages)
            const savedEnabled = localStorage.getItem('bgmEnabled') === 'true';
            const savedVol = parseFloat(localStorage.getItem('bgmVolume') || '0.35');
            vol.value = isNaN(savedVol) ? 0.35 : savedVol;
            audio.volume = vol.value;

            // Resume only if previously enabled (respects browser autoplay policy)
            if (savedEnabled) {
                audio.play().then(() => {
                    toggle.textContent = '❚❚';
                    toggle.setAttribute('aria-pressed', 'true');
                    status.textContent = 'Playing';
                }).catch(() => {
                    toggle.textContent = '▶';
                    toggle.setAttribute('aria-pressed', 'false');
                    status.textContent = 'Click Play';
                });
            } else {
                status.textContent = 'Muted';
            }

            toggle.addEventListener('click', async () => {
                if (audio.paused) {
                    try {
                        await audio.play();
                        toggle.textContent = '❚❚';
                        toggle.setAttribute('aria-pressed', 'true');
                        status.textContent = 'Playing';
                        localStorage.setItem('bgmEnabled', 'true');
                    } catch(e) {
                        status.textContent = 'Click again to allow';
                        toggle.textContent = '▶';
                        toggle.setAttribute('aria-pressed', 'false');
                        localStorage.setItem('bgmEnabled', 'false');
                    }
                } else {
                    audio.pause();
                    toggle.textContent = '▶';
                    toggle.setAttribute('aria-pressed', 'false');
                    status.textContent = 'Paused';
                    localStorage.setItem('bgmEnabled', 'false');
                }
            });

            vol.addEventListener('input', () => {
                audio.volume = vol.value;
                localStorage.setItem('bgmVolume', vol.value);
            });

            // Optional: react to your site theme toggle if it exists
            document.getElementById('modeToggle')?.addEventListener('click', () => {});
        })();
    </script>

<?php
// Flush the buffer only after we've done potential redirects above
ob_end_flush();
