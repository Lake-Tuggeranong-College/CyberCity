<?php
session_start();

// Strict require this file, not 'require_once'
require('config.php');

// Define registered account's access levels
define('USER_ACCESS_LEVEL', 1);
define('ADMIN_ACCESS_LEVEL', 2);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    return;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Website title -->
    <title>Cyber City</title>

    <!-- Bootstrap CSS & Custom CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/moduleList.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/leaderboard.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL; ?>assets/img/CCLogo.png">
</head>

<body>

    <!-- Navigation Bar section -->
    <nav class="navbar navbar-expand-lg navbar-dark navbarCustom navbar-bg-dark">

        <!-- Logo -->
        <img src="<?= BASE_URL; ?>assets/img/CCLogo.png" alt="Cyber City Logo" width="5%" height="5%" </img>

        <!-- Navigation Bar class -->
        <div class="navbar-collapse" id="navbarNav">

            <!-- Navigation bar (left side) -->
            <ul class="navbar-nav me-auto">
                <li class="nav-link active">
                    <a href="<?= BASE_URL; ?>index.php" class="nav-link text-white" style="padding-left: 2rem;">Home</a>
                </li>

                <!-- Check for account logged in that have admin-level of access -->
                <?php if (isset($_SESSION['username']) && $_SESSION['access_level'] == ADMIN_ACCESS_LEVEL): ?>
                    <?php
                    // Fetch user information from the database
                    $userToLoad = $_SESSION['user_id'];
                    $query = "SELECT Score FROM Users WHERE ID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$userToLoad]);
                    $userInformation = $stmt->fetch();

                    $userScore = $userInformation['Score'];
                    ?>

                    <!-- Direct link to 'Leaderboard' page -->
                    <li class="nav-link active">
                        <a href="<?= BASE_URL; ?>pages/leaderboard/leaderboard.php" class="nav-link text-white">Leaderboard</a>
                    </li>

                    <!-- Direct link to 'Challenges' page -->
                    <li class="nav-link active">
                        <a href="<?= BASE_URL; ?>pages/challenges/challengesList.php" class="nav-link text-white">Challenges</a>
                    </li>

                    <!-- Direct link to 'Tutorials' page -->
                    <!-- TODO: Don't do local direct link like this. Make a proper page or so please! -->
                    <li class="nav-link active">
                        <a href="http://10.177.200.71/CyberCityDocs/welcome.html" class="nav-link text-white"
                            target="_blank">Tutorials</a>
                    </li>

                    <!-- Direct link to 'Edit Users' page on admin-level of access -->
                    <li class="nav-link dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">Edit Users</a>

                        <!-- Controlling different user account's accessibility on the website section -->
                        <!-- TODO: What in the bottle flip is this?!? <ul> inside <li>?? -->
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            <!-- Direct link to 'Enabled User List' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/userList.php" class="dropdown-item">Enabled User List</a>
                            </li>

                            <!-- Direct link to 'Disabled User List' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/disabledUsers.php" class="dropdown-item">Disabled User List</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Direct link to 'Modules' page on admin-level of access -->
                    <li class="nav-link dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">Modules</a>

                        <!-- Control different module registered for the CTF challenge on the website section -->
                        <!-- TODO: Again ?!? -->
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            <!-- Direct link to 'Add New Module & Challenge' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/moduleRegister.php" class="dropdown-item">Add New Module & Challenge</a>
                            </li>

                            <!-- Direct link to 'Reset Game' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/resetGame.php" class="dropdown-item">Reset Game</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Direct link to 'Contacts' page on admin-level of access -->
                    <li class="nav-link dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">Contacts</a>

                        <!-- Control the amount of support requests sent to the admin on the website section -->
                        <!-- TODO: Seriously ?!? -->
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            <!-- Direct link to 'View Contact Requests' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/contactpage.php" class="dropdown-item">View Contact Requests</a>
                            </li>

                            <!-- Direct link to 'Read Contact Requests' page on admin-level of access -->
                            <li>
                                <a href="<?= BASE_URL; ?>pages/admin/readContactRequests.php" class="dropdown-item">Read Contact Requests</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Check for account logged in that have user-level of access -->
                <?php elseif (isset($_SESSION['username']) && $_SESSION['access_level'] == USER_ACCESS_LEVEL): ?>
                    <?php
                    // Fetch user information from the database
                    $userToLoad = $_SESSION['user_id'];
                    $query = "SELECT Score FROM Users WHERE ID = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$userToLoad]);
                    $userInformation = $stmt->fetch();

                    $userScore = $userInformation['Score'];
                    ?>

                    <!-- Direct link to 'Leaderboard' page -->
                    <li class="nav-link active">
                        <a href="<?= BASE_URL; ?>pages/leaderboard/leaderboard.php" class="nav-link text-white">Leaderboard</a>
                    </li>

                    <!-- Direct link to 'Challenges' page -->
                    <li class="nav-link active">
                        <a href="<?= BASE_URL; ?>pages/challenges/challengesList.php" class="nav-link text-white">Challenges</a>
                    </li>

                    <!-- Direct link to 'Tutorials' page -->
                    <li class="nav-link active">
                        <a href="http://10.177.200.71/CyberCityDocs/welcome.html" class="nav-link text-white"
                            target="_blank">Tutorials</a>
                    </li>

                        <li class="nav-link active">
                            <a href="https://forms.gle/jgYrmMZesgtVhBZ39" class="nav-link text-white"
                               target="_blank">Feedback</a>
                        </li>


                <?php endif; ?>

                <!-- End of Navigation Bar (left side) -->
            </ul>

            <!-- Navigation Bar (right side) -->
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-link dropdown">
                        <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown"
                           data-bs-toggle="dropdown" aria-expanded="false"><?= htmlspecialchars($_SESSION['username']); ?></a>

                        <!-- Control the amount of support requests sent to the admin on the website section -->
                        <!-- TODO: Seriously ?!? -->
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <!-- Direct link to 'Edit Account' page on user-level of access -->
                            <li class="nav-link active">
                                <a href="<?= BASE_URL; ?>pages/user/editAccount.php" class="dropdown-item">Edit Account</a>
                            </li>

                            <!-- Logged-in account's current score text (both admin & non-admin account) -->
                            <li class="nav-link active">
                                <a class="dropdown-item">Score: <?= htmlspecialchars($userScore); ?></a>
                            </li>
                        </ul>
                    </li>

                    <!-- Logged-out the current logged-in account -->
                    <li class="nav-link active">
                        <a href="<?= BASE_URL; ?>pages/user/logout.php" class="nav-link" style="color: indianred;">Logout</a>
                    </li>

                    <!-- Non-admin account specific navigation bar elements -->
                    <?php if ($_SESSION['access_level'] == USER_ACCESS_LEVEL): ?>
                        <!-- Direct link to 'Contact Us' page on non-admin account -->
                        <li class="nav-link active">
                            <a href="<?= BASE_URL; ?>pages/contactUs/contact.php" class="nav-link text-white">Contact Us</a>
                        </li>
                    <?php endif; ?>

                    <!-- Neither non-admin access level nor admin access level -->
                <?php else: ?>
                    <!-- Register new account / Logged back in current or old account -->
                    <ul class="navbar-nav ms-auto">

                        <!-- Direct link to 'Register' page if users are currently just view through the website -->
                        <li class="nav-link active">
                            <a href="<?= BASE_URL; ?>pages/user/register.php" class="nav-link" style="color: indianred;">Register</a>
                        </li>

                        <!-- Direct link to 'Login' page if users are currently just view through the website -->
                        <li class="nav-link active">
                            <a href="<?= BASE_URL; ?>pages/user/login.php" class="nav-link text-white">Login</a>
                        </li>
                    </ul>
                <?php endif; ?>

                <!-- End of Navigation Bar (right side) -->
            </ul>

            <!-- End of Navigation Bar class -->
        </div>

        <!-- End Navigation Bar -->
    </nav>

    <!-- Best approach to comment out PHP mixed with HTML code that also have comment with it -->
    <?php /* ?>

    Flash confirm message to indicating users successfully logged-in/registered into the website
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="position-absolute top-15 end-0"><?= htmlspecialchars($_SESSION['flash_message']); ?></div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php // */ ?>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <?php $message = $_SESSION['flash_message']; ?>

        <!-- Flash message positoned on the top (?) when confirming the needed condition -->
        <div class="position-static"><?= $message; ?></div>

        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Boostrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

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
