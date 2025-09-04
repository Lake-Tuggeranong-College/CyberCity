<?php
// includes/template.php (top of file)

// Start session only if not already active
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Optional: buffering can help avoid "headers already sent" if
// anything below echoes before redirects on other pages.
// ob_start();

require_once __DIR__ . '/config.php';  // keep this above any HTML output

define('USER_ACCESS_LEVEL', 1);
define('ADMIN_ACCESS_LEVEL', 2);

/* ---------------------- Utilities ---------------------- */
function set_flash(string $type, string $text): void
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

function take_flash(): ?array
{
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function sanitise_data(string $data): string
{
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Gatekeeper for page access.
 * Set the allows for this page below where it's called.
 */
function authorisedAccess(bool $allow_unauth, bool $allow_user, bool $allow_admin): bool
{
    if (!isset($_SESSION["username"])) {
        if (!$allow_unauth) {
            set_flash('danger', 'Access Denied');
            return false;
        }
        return true;
    }

    $level = $_SESSION["access_level"] ?? null;

    if ($level === USER_ACCESS_LEVEL && !$allow_user) {
        set_flash('danger', 'Access Denied');
        return false;
    }
    if ($level === ADMIN_ACCESS_LEVEL && !$allow_admin) {
        set_flash('danger', 'Access Denied');
        return false;
    }
    return true;
}

/* ---------------------- Page guard ---------------------- */
/*
   Choose one for this page:
   - Public:     authorisedAccess(true,  true,  true)
   - Members:    authorisedAccess(false, true,  true)
   - Admin only: authorisedAccess(false, false, true)
*/
if (!authorisedAccess(true, true, true)) { // change flags as needed
    header("Location: " . BASE_URL . "index.php");
    exit;
}

/* ---------------------- Navbar helpers ---------------------- */
$userScore = 0;
if (isset($_SESSION['username'])) {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $stmt = $conn->prepare("SELECT Score FROM Users WHERE ID = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['Score'])) $userScore = (int)$row['Score'];
        }
    } catch (Throwable $e) {
        // Optional: log error; keep $userScore = 0
    }
}
$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber City</title>

    <script type="text/javascript">
        function doUnauthRedirect() {
            location.replace("http://10.177.200.71/index.html");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Bootstrap + CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/moduleList.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/leaderboard.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/editAccount.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL; ?>assets/img/CCLogo.png">
</head>
<body class="bg-light text-black">
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand">Cyber City</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link text-black dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Accessibility Features
                    </a>
                    <ul class="dropdown-menu p-3" style="min-width: 300px;">
                            <div>
                                <h6>Fonts</h6>
                                <button class="btn btn-sm btn-outline-primary accessibility-font" data-size="small">Small</button>
                                <button class="btn btn-sm btn-outline-primary accessibility-font" data-size="medium">Medium</button>
                                <button class="btn btn-sm btn-outline-primary accessibility-font" data-size="large">Large</button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div>
                                <h6>Line Spacing</h6>
                                <button class="btn btn-sm btn-outline-primary accessibility-line" data-spacing="1">Normal</button>
                                <button class="btn btn-sm btn-outline-primary accessibility-line" data-spacing="1.5">1.5x</button>
                                <button class="btn btn-sm btn-outline-primary accessibility-line" data-spacing="2">2x</button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div>
                                <h6>High Contrast Mode</h6>
                                <button id="toggleContrast" class="btn btn-sm btn-outline-primary">Toggle High Contrast</button>
                            </div>
                        </li>
                    </ul>
                <li class="nav-item"><a href="<?= BASE_URL; ?>index.php" class="nav-link text-black"
                                        style="padding-left: 2rem;">Home</a></li>
                <li class="nav-item"><a href="<?= BASE_URL; ?>pages/leaderboard/leaderboard.php"
                                        class="nav-link text-black">Leaderboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link text-black dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Project Version
                    </a>

                    <ul class="dropdown-menu">
                        <?php
                        // Database connection using PDO
                        try {
                            // Query to get projects
                            $stmt = $conn->query("SELECT project_id, project_name FROM CyberCity.Projects");

                            // Gene<ul class="navbar-nav me-auto mb-2 mb-lg-0">rate list items
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<li><a class="dropdown-item" href="' . BASE_URL . 'pages/challenges/challengesList.php?projectID=' . $row['project_id'] . '">' . htmlspecialchars($row['project_name']) . '</a></li>';
                            }
                        } catch (PDOException $e) {
                            echo '<li><span class="dropdown-item text-danger">Error loading projects</span></li>';
                            // Optionally log the error: error_log($e->getMessage());
                        }
                        ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="http://10.177.202.196/CyberCityDocs/welcome.html" class="nav-link text-black"
                               target="_blank">Tutorials</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="https://forms.gle/jgYrmMZesgtVhBZ39" class="nav-link text-black"
                                        target="_blank">Feedback</a></li>

                <!-- Admin Panel -->
                <?php if (isset($_SESSION['username']) && ($_SESSION['access_level'] ?? null) == ADMIN_ACCESS_LEVEL): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link text-black dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Admin Panel
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= BASE_URL; ?>pages/admin/userList.php" class="dropdown-item">User
                                List</a></li>

                        <hr class="dropdown-divider">
                </li>
                <li><a href="<?= BASE_URL; ?>pages/admin/contactpage.php" class="dropdown-item">View Contact
                        Requests</a></li>

                <hr class="dropdown-divider">
                </li>
                <li><a href="<?= BASE_URL; ?>pages/admin/challengeRegister.php" class="dropdown-item">
                        Create New Challenges</a></li>
<!--                <hr class="dropdown-divider">-->
                <li><a href="<?= BASE_URL; ?>pages/admin/createCategory.php" class="dropdown-item">
                        Create New Category</a></li>
                <hr class="dropdown-divider">
                <li><a href="<?= BASE_URL; ?>pages/admin/resetGame.php" class="dropdown-item">Reset Game</a>
                </li>
            </ul>
            </li>
            <?php endif; ?>
            </ul>

            <!-- Right -->
            <ul class="navbar-nav ms-auto">
                <button id="modeToggle" class="btn btn-outline-secondary mode-toggle-btn">Switch to Dark Mode</button>

                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-link dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-black" id="navbarDropdown"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a href="<?= BASE_URL; ?>pages/user/editAccount.php" class="dropdown-item">Edit
                                    Account</a></li>
                            <li><a class="dropdown-item">Score: <?= htmlspecialchars((string)$userScore); ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-link active"><a href="<?= BASE_URL; ?>pages/user/logout.php" class="nav-link"
                                                   style="color:#000;">Logout</a></li>
                    <?php if (($_SESSION['access_level'] ?? null) == USER_ACCESS_LEVEL): ?>
                        <li class="nav-link active"><a href="<?= BASE_URL; ?>pages/contactUs/contact.php"
                                                       class="nav-link text-white">Contact Us</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-link active"><a href="<?= BASE_URL; ?>pages/user/register.php" class="nav-link"
                                                   style="color:indianred;">Register</a></li>
                    <li class="nav-link active"><a href="<?= BASE_URL; ?>pages/user/login.php"
                                                   class="nav-link text-black">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash -->
<?php if ($flash): ?>
    <?php
    $type = preg_replace('/[^a-z]/', '', $flash['type']); // simple whitelist
    $text = htmlspecialchars($flash['text'], ENT_QUOTES, 'UTF-8');
    ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $type ?> mb-3" role="alert"><?= $text ?></div>
    </div>
<?php endif; ?>

<!-- Your page content goes here -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
    const modeToggleBtn = document.getElementById('modeToggle');
    const body = document.body;

    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        if (savedTheme === 'bg-dark text-white') {
            modeToggleBtn.textContent = 'Switch to Light Mode';
            body.classList.add('bg-dark', 'text-white');
            updateWideBoxClasses('dark');
        } else {
            body.classList.add('bg-light', 'text-black');
            modeToggleBtn.textContent = 'Switch to Dark Mode';
            updateWideBoxClasses('light');
        }
    }

    function updateWideBoxClasses(theme) {
        const wideBoxes = document.querySelectorAll(theme === 'light' ? '.wideBoxDark' : '.wideBox');
        wideBoxes.forEach(box => {
            box.classList.replace(theme === 'light' ? 'wideBoxDark' : 'wideBox',
                theme === 'light' ? 'wideBox' : 'wideBoxDark');
        });
    }

    modeToggleBtn.addEventListener('click', function () {
        if (body.classList.contains('bg-light')) {
            body.classList.replace('bg-light', 'bg-dark');
            body.classList.replace('text-black', 'text-white');
            updateWideBoxClasses('dark');
            modeToggleBtn.textContent = 'Switch to Light Mode';
            localStorage.setItem('theme', 'bg-dark text-white');
        } else {
            body.classList.replace('bg-dark', 'bg-light');
            body.classList.replace('text-white', 'text-black');
            updateWideBoxClasses('light');
            modeToggleBtn.textContent = 'Switch to Dark Mode';
            localStorage.setItem('theme', 'bg-light text-black');
        }
    });
</script>
<script>
    // Load saved preferences or set defaults
    const savedFont = localStorage.getItem('accessibilityFont') || 'medium';
    const savedLineSpacing = localStorage.getItem('accessibilityLineSpacing') || '1';
    const savedContrast = localStorage.getItem('accessibilityContrast') === 'true';

    function applyAccessibilitySettings() {
        document.body.classList.remove('font-small', 'font-medium', 'font-large');
        document.body.classList.add('font-' + savedFont);

        document.body.classList.remove('line-spacing-1', 'line-spacing-1-5', 'line-spacing-2');
        if (savedLineSpacing === '1') {
            document.body.classList.add('line-spacing-1');
        } else if (savedLineSpacing === '1.5') {
            document.body.classList.add('line-spacing-1-5');
        } else if (savedLineSpacing === '2') {
            document.body.classList.add('line-spacing-2');
        }

        if (savedContrast) {
            document.body.classList.add('high-contrast');
        } else {
            document.body.classList.remove('high-contrast');
        }
    }

    applyAccessibilitySettings();

    // Highlight active buttons
    document.querySelectorAll('.accessibility-font').forEach(button => {
        if (button.getAttribute('data-size') === savedFont) {
            button.classList.add('active');
        }
    });
    document.querySelectorAll('.accessibility-line').forEach(button => {
        if (button.getAttribute('data-spacing') === savedLineSpacing) {
            button.classList.add('active');
        }
    });
    if (savedContrast) {
        document.getElementById('toggleContrast').classList.add('active');
    }

    // Font size buttons
    document.querySelectorAll('.accessibility-font').forEach(button => {
        button.addEventListener('click', () => {
            const size = button.getAttribute('data-size');
            localStorage.setItem('accessibilityFont', size);
            location.reload(); // reload to apply changes cleanly
        });
    });

    // Line spacing buttons
    document.querySelectorAll('.accessibility-line').forEach(button => {
        button.addEventListener('click', () => {
            const spacing = button.getAttribute('data-spacing');
            localStorage.setItem('accessibilityLineSpacing', spacing);
            location.reload();
        });
    });

    // High contrast toggle
    document.getElementById('toggleContrast').addEventListener('click', () => {
        const current = localStorage.getItem('accessibilityContrast') === 'true';
        localStorage.setItem('accessibilityContrast', !current);
        location.reload();
    });
</script>
</body>
</html>
