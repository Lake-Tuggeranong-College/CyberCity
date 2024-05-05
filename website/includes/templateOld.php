<?php require_once 'config.php'; ?>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/Styles.css">
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-sm navbar-dark navbar_Dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
            <img src="/assets/img/CCLogo.png" alt="" width="100" height="100">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link navbar_Dark" href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <!--<li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>-->
                <?php
                $accessLevel = 2;
                if (isset($_SESSION["username"])) {
                    $userToLoad = $_SESSION["user_id"];
                    $sql = $conn->query("SELECT Score FROM Users WHERE ID= " . $userToLoad);
                    $userInformation = $sql->fetch();
                    $userScore = $userInformation["Score"];

                    echo '
                    <!--<li class="nav-item"><a class="nav-link" href="flagClaimer.php">Flag Claimer</a></li>-->
                    <!--what is object-oriented programming?-->
                    <li class="nav-item"><a class="nav-link navbar_Dark" href="' . BASE_URL .'pages/leaderboard/leaderboard.php">Leaderboard</a></li>  
                    <li class="nav-item"><a class="nav-link navbar_Dark" href="' . BASE_URL .'pages/challenges/challengesList.php">Challenges</a></li>  
                    <li class="nav-item"><a class="nav-link navbar_Dark" href="' . BASE_URL .'pages/tutorials/tutorialList.php">Tutorials</a></li>             
                    <li class="nav-item"><a class="nav-link navbar_Dark" href="' . BASE_URL .'pages/contactUs/contact.php">Contact&nbsp;us</a>  <!--why doesnt this work-->                                                                                   
              
                    <!--IT TOOK 3 HOURS JUST TO GET THIS FAR, AND IT DOESNT WORK-->   
                    <li class="nav-item" ><a class="nav-link navbar_Dark" href="' . BASE_URL .'pages/documentation/TODO">Documentation</a></li> 
                    <li class="nav-item" ><p class="navbar_Dark">Logged&nbsp;in&nbsp;as:&nbsp;' . $_SESSION["username"] . '&nbsp;&nbsp;</p></li> 
                    <li class="nav-item" ><p class="navbar_Dark">Score:&nbsp;' . $userScore . '</p></li>   
                    <li class="nav-item" ><a class="nav-link navbar_Dark" href="' . BASE_URL .'/pages/user/logout.php">Logout</a></li> 
                    ';

                    if ($_SESSION["access_level"] == $accessLevel) {

                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle navbar_Dark" href="#" id="navbarDropdown"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                Administrator Functions
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <h3 style="padding-left: 15px">Edit Users</h3>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/userList.php">Enabled User List</a>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/disabledUsers.php">Disabled User List</a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/moduleRegister.php">Add New Module & Challenge</a>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/resetGame.php">Reset Game</a>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/contactpage.php">View Contact requests</a>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/admin/readContactRequests.php">Read Contact Requests</a>
                            </ul>
                        </li>
                        <?php
                    }

                } else {
                    echo '
                    <li class="nav-itemr/register.php">Register</a></li>
                    '; #Register button (when NOT logged in)"><a class="nav-link navbar_Dark" href="' . BASE_URL . 'pages/use
                    echo '
                    <li class="nav-item"><a class="nav-link navbar_Dark" href="' . BASE_URL . 'pages/user/login.php">Login</a></li>
                    '; #Login button (when NOT logged in)


                }
                ?>

            </ul>

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
<script src="/assets/js/bootstrap.bundle.js"></script>
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
    } else {

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
    }

    // otherwise, let them through
    return true;
}



?>
