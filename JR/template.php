

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
            <img src="images/logo.png" alt="" width="80" height="80">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
            </ul>
            <?php if (isset($_SESSION["username"])) {
                echo "<div class='alert alert-success d-flex'><span>Welcome, " . $_SESSION["username"] . "<br><a href='logout.php'>Logout</a></span></div>";
            } else {
                echo "<div class='alert alert-info d-flex'><a href='login.php'>Sign In</a></div>";
                echo "<div class='alert alert-info d-flex'><a href='register.php'>Sign up</a></div>";

            }

            ?>
        </div>
    </div>
</nav>

</body>
</html>
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
    echo "<footer>This page was last modified: " . date("F d Y H:i:s.", filemtime("index.php")) . "</footer>";
}

?>


<!--<form action="--><?php //echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><!--" method="post" enctype="multipart/form-data">-->
<!--    <div class="container-fluid">-->
<!--        <div class="row">-->
<!--            <!--Customer Details-->-->
<!---->
<!--            <div class="col-md-12">-->
<!--                <h2>Account Details</h2>-->
<!--                <p>Please enter wanted username and password:</p>-->
<!--                <p>User Name<input type="text" name="username" class="form-control" required="required"></p>-->
<!--                <p>Password<input type="password" name="password" class="form-control" required="required"></p>-->
<!---->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <input type="submit" name="formSubmit" value="Submit">-->
<!--</form>-->

