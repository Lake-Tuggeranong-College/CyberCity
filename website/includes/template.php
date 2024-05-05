<?php require_once 'config.php'; ?>
<head>
    <title>CyberCity</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbarCustom navbar-bg-dark">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php"></a>
        <img src="<?php echo BASE_URL; ?>assets/img/CCLogo.png" alt="" width="100" height="100">
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto "> <!--Left side of navbar-->
            <li class="nav-item active">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php">Home</a>
            </li>
            <?php
            $accessLevel = 2;
            if(isset($_SESSION['username'])) {
                $userToLoad = $_SESSION['user_id'];
                $sql = $conn->query("SELECT Score FROM Users WHERE ID = " . $userToLoad);
                $userInformation = $sql->fetch();
                $userScore = $userInformation['Score'];
            echo'
            
            <li class="nav-item active">
                <a class="nav-link text-white" href="' . BASE_URL .'pages/leaderboard/leaderboard.php">Leaderboard</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link text-white" href="' . BASE_URL .'pages/challenges/challengesList.php">Challenges</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link text-white" href="' . BASE_URL .'pages/tutorials/tutorialList.php">Tutorials</a></a>
            </li>
            ';

                if ($_SESSION["access_level"] == $accessLevel) {
                    echo '
                <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Administrator Functions
                        </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <h3 style="padding-left: 15px">Edit Users</h3>
                        <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/userList.php">Enabled User List</a>
                        <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/disabledUsers.php">Disabled User List</a>
                    <div class="dropdown-divider"></div>
                         <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/moduleRegister.php">Add New Module & Challenge</a>
                         <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/resetGame.php">Reset Game</a>
                         <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/contactpage.php">View Contact requests</a>
                         <a class="dropdown-item" href="' . BASE_URL . 'pages/admin/readContactRequests.php">Read Contact Requests</a>
                    </ul>
                </li>
        </ul>'; }


        echo '</ul>
        <ul class="navbar-nav ms-auto"> <!--Right side of navbar-->
            <li class="nav-item active">
                <a class="nav-link text-white" href="' . BASE_URL .'pages/contactUs/contact.php">Contact&nbsp;us</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link text-white" >Logged&nbsp;in&nbsp;as:&nbsp;' . $_SESSION["username"] . '</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link text-white" >Score:&nbsp;' . $userScore . '</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" style = "color: indianred" href="' . BASE_URL .'/pages/user/logout.php">Logout</a></a>
            </li>
        </ul>
        ';
        ?>

        <?php
        } else {
        echo '
        </ul>
        <ul class="navbar-nav ms-auto"> <!--Right side of navbar-->
            <!-- #Register button (when NOT logged in)"><a class="nav-link navbar_Dark" href="' . BASE_URL . 'pages/use -->
            <li class="nav-item active"><a class="nav-link text-white" href="' . BASE_URL . 'pages/user/register.php">Register</a></li>
      
            <!-- Login button (when NOT logged in) -->
            <li class="nav-item active"><a class="nav-link text-white" href="' . BASE_URL . 'pages/user/login.php">Login</a></li>
        </ul>
        ';}
        ?>
        </ul>

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


<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.js"></script>


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
