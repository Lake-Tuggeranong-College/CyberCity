<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(true, false, false)) {
    header("Location:../../index.php");
}

?>

<title>Cyber City - Login</title>

<h1 style = 'text-align: center'>Login</h1>
<h2 style = 'text-align: center'>Login to your account here</h2>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid centerBox">
        <div class="row">
            <!--Customer Details-->

            <div class="col-md-12">
                <h2>Account Details</h2>
                <p>Please enter your Username and Password:</p>
                <p>User Name<input type="text" name="username" class="form-control" required="required"></p>
                <p>Password<input type="password" name="password" class="form-control" required="required"></p>
                <input type="submit" name="formSubmit" value="Submit">
            </div>
        </div>
    </div>

</form>


<?php
//if (isset($_POST['login'])) {


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitise_data($_POST['username']);
    $password = sanitise_data($_POST['password']);

    $query = $conn->query("SELECT COUNT(*) as count FROM `Users` WHERE `Username` ='$username'");
    $row = $query->fetch();
    $count = $row[0];

    if ($count > 0) {
        $query = $conn->query("SELECT * FROM `Users` WHERE `Username`='$username'");
        $row = $query->fetch();
        if ($row[4] == 1) {
            if (password_verify($password, $row[2])) {
                // successful log on.
                $_SESSION["user_id"] = $row[0];
                $_SESSION["username"] = $row[1];
                $_SESSION['access_level'] = $row[3];
                $_SESSION["flash_message"] = "<div class='bg-success'>Login Successful</div>";
                header("Location:../../index.php");
            } else {
                // unsuccessful log on.
                echo "<div class='alert alert-danger'>Invalid Username or Password. <a href='../contactUs/contact.php'>Contact Us</a></div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Account Disabled. <a href='../contactUs/contact.php'>Contact Us</a></div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid Username or Password. <a href='../contactUs/contact.php'>Contact Us</a></div>";
    }
}

?>

</body>
</html>



<!--
            $_SESSION["user_id"] = $row[0];
            $_SESSION["username"] = $row[1];
            $_SESSION['access_level'] = $row[3];
            echo "successful";
//            header("location:profile.php");
        } else {
            echo "<div class='alert alert-danger'>Invalid username or password</div>";
        }
-->