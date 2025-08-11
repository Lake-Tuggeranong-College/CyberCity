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

// Helper for safe HTML
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Render a single challenge card (always shows a card, even without image).
 */
function createChallengeCard(array $challengeData): void
{
    // Extract with sensible fallbacks
    $challengeID       = $challengeData['ID'];
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
        <div class="card h-100">
            <img src="<?= e($imgSrc) ?>" class="card-img-top" alt="<?= e($challengeTitle) ?>"
                 width="100" height="200" style="object-fit: cover;">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-2"><?= e($challengeTitle) ?></h5>
                <p class="card-text text-muted mb-3">Points: <?= $pointsValue ?></p>
                <a href="<?= e($href) ?>" class="btn btn-warning mt-auto">Start Challenge</a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Fetch and render challenges grouped by category.
 */
function displayResultsByCategory(PDO $conn, int $projectID): void
{
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

    $currentCategory = null;
    $openRow = false;

    foreach ($rows as $row) {
        // Start new category block when the name changes
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

        createChallengeCard($row);
    }

    if ($openRow) {
        echo "</div>";
    }
}
?>

    <link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/moduleList.css">

    <h1>Challenges</h1>

    <div class="container-fluid">
        <?php displayResultsByCategory($conn, $projectID); ?>
    </div>

    </body>
    </html>
<?php
// Flush the buffer only after we've done potential redirects above
ob_end_flush();
