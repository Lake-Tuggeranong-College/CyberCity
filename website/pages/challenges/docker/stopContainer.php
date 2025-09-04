<?php
/**
 * Drop-in replacement: pages/challenges/docker/stopContainer.php
 * Requires: includes/config.php sets up $conn (PDO) and session.
 */
declare(strict_types=1);

require('../../../includes/config.php');   // must define $conn (PDO) and session
/** @var PDO $conn */

// ---------- Helpers ----------
function json_out(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

// ---------- Method check ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Allow CORS preflight if needed
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        exit; // 204
    }
    json_out(405, ['ok' => false, 'error' => 'POST required']);
}

// ---------- Parse body (form or JSON) ----------
$payload = $_POST;
if (empty($payload)) {
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
        $tmp = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
            $payload = $tmp;
        }
    }
}

$challengeID = $payload['challengeID'] ?? $payload['dChallengeID'] ?? null;
$userID      = $payload['userID'] ?? null;

// ---------- Validate ----------
if ($challengeID === null || $challengeID === '') {
    json_out(400, ['ok' => false, 'error' => 'Missing challengeID']);
}
if ($userID === null || $userID === '') {
    json_out(400, ['ok' => false, 'error' => 'Missing userID']);
}

$challengeID = filter_var($challengeID, FILTER_VALIDATE_INT);
$userID      = filter_var($userID, FILTER_VALIDATE_INT);

if ($challengeID === false) json_out(400, ['ok' => false, 'error' => 'challengeID must be an integer']);
if ($userID === false)      json_out(400, ['ok' => false, 'error' => 'userID must be an integer']);

// ---------- AuthZ: ensure the caller is the same logged-in user ----------
session_start();
$sessionUser = $_SESSION['user_id'] ?? null;
if (!$sessionUser || (int)$sessionUser !== (int)$userID) {
    // If you prefer to allow admins, add your admin check here.
    json_out(403, ['ok' => false, 'error' => 'Not authorised for this user']);
}

// ---------- Delete row (idempotent) ----------
try {
    $stmt = $conn->prepare("
        DELETE FROM DockerContainers
        WHERE challengeID = :c AND userID = :u
        LIMIT 1
    ");
    $stmt->execute([':c' => $challengeID, ':u' => $userID]);

    $deleted = $stmt->rowCount();

    // 200 even if already stopped — idempotent success
    json_out(200, [
        'ok'           => true,
        'message'      => $deleted ? 'Container stop requested.' : 'No running container found.',
        'deleted_rows' => $deleted
    ]);
} catch (Throwable $e) {
    // Avoid leaking DB details in production; keep message concise
    json_out(500, ['ok' => false, 'error' => 'Database error']);
}
