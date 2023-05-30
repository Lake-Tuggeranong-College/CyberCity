<?php require_once 'config.php'; ?>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-sm navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="images/Logo2.jpg" alt="" width="100" height="100">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="moduleInformation.php">Modules</a></li>
                <li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>

                <?php
                $accessLevel = 2;
                if (isset($_SESSION["username"])) {
                    echo '
                    <li class="nav-item"><a class="nav-link" href="flagClaimer.php">Flag Claimer</a></li>
                    ';
                    if ($_SESSION["access_level"] == $accessLevel) {

                        ?>
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Administrator Functions
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="userSearch.php">User Search</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="moduleRegister.php">Add New Module</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="flagMaker.php">Add New Flag</a>
                        <?php
                    }
                    ?>
                    </ul>
                    </li>

                    <?php
                } else {
                    echo '
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    ';
                    echo '
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    ';


                }
                ?>

            </ul>
        </div>
        <?php
        if (isset($_SESSION["username"])) {
            echo "<div class='alert alert-success d-flex'><span>Welcome, " . $_SESSION["username"] . "<br><a href='logout.php'>Logout</a></span></div>";
        }
        ?>
    </div>
</nav>
<?php
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
//    echo $message;
    ?>
    <div class="position-absolute bottom-0 end-0">
        <?= $message ?>

    </div>


    <?php
}
?>
<script src="js/bootstrap.bundle.js"></script>
<?php
function sanitise_data($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function outputFooter()
{
    date_default_timezone_set('Australia/Canberra');
    $filename = basename($_SERVER["SCRIPT_FILENAME"]);
    $footer = "This page was last modified: " . date("F d Y H:i:s.", filemtime($filename));
    return $footer;
}


/*
 * This function confirms if the user is authorised to access individual pages or not.
 * @params
 * @return  true if user is authorised to access page.
 *          false if user is not authorised to access page.
 */
function authorisedAccess($unauthorisedUsers, $users, $admin)
{
    // Unauthenticated User
    if (!isset($_SESSION["username"])) { // user not logged in
        if ($unauthorisedUsers == false) {
            $_SESSION['flash_message'] = "<div class='bg-danger'>Access Denied</div>";
            return false;
        }
    }

    // Regular User
    if ($_SESSION["access_level"] == 1) {
        if ($users == false) {
            $_SESSION['flash_message'] = "<div class='bg-danger'>Access Denied</div>";
            return false;
        }
    }

    // Administrators
    if ($_SESSION["access_level"] == 2) {
        if ($admin == false) {
            return false;
        }
    }

    // otherwise, let them through
    return true;
}

?>
