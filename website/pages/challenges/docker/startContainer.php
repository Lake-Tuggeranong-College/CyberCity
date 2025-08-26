<?php
require('../../../includes/config.php'); // must set up $conn (PDO)
/** @var PDO $conn */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Error: POST required.');
}

// --- Gather input (form or JSON) ---
$payload = $_POST;

if (empty($payload)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $payload = $json;
        }
    }
}

// Normalise keys: accept challengeID or dChallengeID
$challengeID = $payload['challengeID'] ?? $payload['dChallengeID'] ?? null;
$userID      = $payload['userID'] ?? null;

// Validate presence with distinct messages
if ($challengeID === null || $challengeID === '') {
    http_response_code(400);
    exit('Error: Missing data for challengeID.');
}
if ($userID === null || $userID === '') {
    http_response_code(400);
    exit('Error: Missing data for userID.');
}

// Coerce and validate integers
$challengeID = filter_var($challengeID, FILTER_VALIDATE_INT);
$userID      = filter_var($userID, FILTER_VALIDATE_INT);

if ($challengeID === false) {
    http_response_code(400);
    exit('Error: challengeID must be an integer.');
}
if ($userID === false) {
    http_response_code(400);
    exit('Error: userID must be an integer.');
}

// Insert record — only challengeID, userID, and timeInitialised
$current_date = date('Y-m-d H:i:s');

try {
    $stmt = $conn->prepare("
        INSERT INTO DockerContainers (challengeID, userID, timeInitialised)
        VALUES (:challengeID, :userID, :timeInitialised)
    ");
    $stmt->execute([
        ':challengeID'     => $challengeID,
        ':userID'          => $userID,
        ':timeInitialised' => $current_date
    ]);

    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'inserted_id'     => $conn->lastInsertId(),
        'timeInitialised' => $current_date
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    // Use verbose error only for debugging; comment this out later
    echo 'DB error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
