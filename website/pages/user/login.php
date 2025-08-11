<?php
// pages/user/login.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../includes/config.php'; // must define $conn (PDO) and BASE_URL

// ---------- optional debug ----------
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
// ------------------------------------

// Already logged in? Go home
if (!empty($_SESSION['username'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errors[] = 'Please enter both username and password.';
    } else {
        try {
            // Aligns with your schema: ID, Username, HashedPassword (bcrypt), AccessLevel
            $stmt = $conn->prepare('SELECT ID, Username, HashedPassword, AccessLevel FROM Users WHERE Username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($DEBUG && $user) {
                echo "<pre style='background:#111;color:#0f0;padding:8px;border-radius:6px'>"
                    . "DEBUG: user loaded; hash present=" . (isset($user['HashedPassword']) ? 'yes' : 'no')
                    . "</pre>";
            }

            if ($user && isset($user['HashedPassword']) && password_verify($password, (string)$user['HashedPassword'])) {
                // Success: set session and redirect
                $_SESSION['username']     = (string)$user['Username'];
                $_SESSION['user_id']      = (int)$user['ID'];
                $_SESSION['access_level'] = (int)($user['AccessLevel'] ?? 1);

                $_SESSION['flash'] = ['type' => 'success', 'text' => 'Welcome back!'];
                header('Location: ' . BASE_URL . 'index.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password.';
            }
        } catch (Throwable $e) {
            if ($DEBUG) {
                echo "<pre style='background:#111;color:#f66;padding:8px;border-radius:6px'>"
                    . "DEBUG ERROR: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . "</pre>";
            }
            $errors[] = 'Login failed. Please try again shortly.';
        }
    }
}

// From here down it’s safe to output HTML (and include your template)
require_once __DIR__ . '/../../includes/template.php';
?>

<div class="container mt-4">
    <?php if (!empty($_SESSION['flash'])):
        $type = preg_replace('/[^a-z]/', '', $_SESSION['flash']['type']);
        $text = htmlspecialchars($_SESSION['flash']['text'], ENT_QUOTES, 'UTF-8');
        unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= $type ?>" role="alert"><?= $text ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <h1 class="mb-3">Login</h1>

    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . ($DEBUG ? '?debug=1' : '') ?>">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input id="username" name="username" type="text" class="form-control"
                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Login</button>
        <?php if ($DEBUG): ?><span class="ms-2 badge text-bg-secondary">Debug ON</span><?php endif; ?>
    </form>
</div>
