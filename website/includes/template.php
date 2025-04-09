<?php
session_start();
require('config.php');

define('USER_ACCESS_LEVEL', 1);
define('ADMIN_ACCESS_LEVEL', 2);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    return;
}

function fetchUserScore($conn, $userId) {
    $query = "SELECT Score FROM Users WHERE ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $userInformation = $stmt->fetch();
    return $userInformation['Score'];
}

$userScore = isset($_SESSION['user_id']) ? fetchUserScore($conn, $_SESSION['user_id']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/styles.css">
    <link rel="icon" href="<?= BASE_URL; ?>assets/img/CCLogo.png">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL; ?>index.php">
            <img src="<?= BASE_URL; ?>assets/img/CCLogo.png" alt="Cyber City Logo" width="40%" height="40%">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL; ?>index.php">Home</a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                    <?php if ($_SESSION['access_level'] == ADMIN_ACCESS_LEVEL): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= BASE_URL; ?>pages/leaderboard/leaderboard.php">Leaderboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="challengesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Challenges</a>
                            <ul class="dropdown-menu" aria-labelledby="challengesDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/challenges/challengesList.php?projectID=1">2024 Project</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/challenges/challengesList.php?projectID=2">2025 Project</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="http://10.177.200.71/CyberCityDocs/welcome.html" target="_blank">Tutorials</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="editUsersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Edit Users</a>
                            <ul class="dropdown-menu" aria-labelledby="editUsersDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/userList.php">Enabled User List</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/disabledUsers.php">Disabled User List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="modulesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Modules</a>
                            <ul class="dropdown-menu" aria-labelledby="modulesDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/moduleRegister.php">Add New Module & Challenge</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/resetGame.php">Reset Game</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="contactsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Contacts</a>
                            <ul class="dropdown-menu" aria-labelledby="contactsDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/contactpage.php">View Contact Requests</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/admin/readContactRequests.php">Read Contact Requests</a></li>
                            </ul>
                        </li>
                    <?php elseif ($_SESSION['access_level'] == USER_ACCESS_LEVEL): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= BASE_URL; ?>pages/leaderboard/leaderboard.php">Leaderboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="challengesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Challenges</a>
                            <ul class="dropdown-menu" aria-labelledby="challengesDropdown">
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/challenges/challengesList.php?projectID=1">2024 Project</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/challenges/challengesList.php?projectID=2">2025 Project</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="http://10.177.200.71/CyberCityDocs/welcome.html" target="_blank">Tutorials</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="https://forms.gle/jgYrmMZesgtVhBZ39" target="_blank">Feedback</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <button id="modeToggle" class="btn btn-outline-secondary">Switch to Dark Mode</button>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= htmlspecialchars($_SESSION['username']); ?></a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= BASE_URL; ?>pages/user/editAccount.php">Edit Account</a></li>
                            <li><a class="dropdown-item">Score: <?= htmlspecialchars($userScore); ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= BASE_URL; ?>pages/user/logout.php">Logout</a>
                    </li>
                    <?php if ($_SESSION['access_level'] == USER_ACCESS_LEVEL): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= BASE_URL; ?>pages/contactUs/contact.php">Contact Us</a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= BASE_URL; ?>pages/user/register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= BASE_URL; ?>pages/user/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="position-static"><?= $_SESSION['flash_message']; ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modeToggleBtn = document.getElementById('modeToggle');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        body.classList.add(...savedTheme.split(' '));
        modeToggleBtn.textContent = savedTheme.includes('bg-dark') ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }
     modeToggleBtn.addEventListener('click', function () {
        if (body.classList.contains('bg-light')) {
            // Switch to dark mode
            body.classList.replace('bg-light', 'bg-dark');
            body.classList.replace('text-black', 'text-white');
            modeToggleBtn.textContent = 'Switch to Light Mode'; // Update button text
            // Save the dark mode preference in localStorage
            localStorage.setItem('theme', 'bg-dark text-white');
        } else {
            // Switch to light mode
            body.classList.replace('bg-dark', 'bg-light');
            body.classList.replace('text-white', 'text-black');
            modeToggleBtn.textContent = 'Switch to Dark Mode'; // Update button text
            // Save the light mode preference in localStorage
            localStorage.setItem('theme', 'bg-light text-black');
        }
    });
</script>

<?php
/**
 * sanitise input data to prevent XSS and other attacks
 *
 * @param string $data The data to sanitise
 * @return string The sanitised data
 */
function sanitise_data($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Confirm if the user is authorised to access individual pages
 *
 * @param bool $unauthorisedUsers Allow unauthorised users
 * @param bool $users Allow regular users
 * @param bool $admin Allow administrators
 * @return bool True if user is authorised, false otherwise
 */
function authorisedAccess($unauthorisedUsers, $users, $admin)
{
    // Unauthenticated User
    if (!isset($_SESSION["username"])) {
        if (!$unauthorisedUsers) {
            $_SESSION['flash_message'] = "<div class='bg-danger'>Access Denied</div>";
            return false;
        }
    } else {
        // Regular User
        if ($_SESSION["access_level"] == USER_ACCESS_LEVEL && !$users) {
            $_SESSION['flash_message'] = "<div class='bg-danger'>Access Denied</div>";
            return false;
        }

        // Administrators
        if ($_SESSION["access_level"] == ADMIN_ACCESS_LEVEL && !$admin) {
            return false;
        }
    }

    // Otherwise, let them through
    return true;
}

?>
</body>

</html>
