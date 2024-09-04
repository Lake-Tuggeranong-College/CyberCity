<?php
include_once "../../includes/template.php";

if (!authorisedAccess(true, false, false)) {
    header("Location:../../index.php");
}
?>

<title>Cyber City - Login</title>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid centerBox">
        <div class="row">
            <!--Customer Details-->
            <div class="col-md-12">
                <h2>Log in</h2>
                <p>Please enter your Username and Password:</p>
                <p>User Name<input type="text" name="username" class="form-control" required="required"></p>
                <p>Password<input type="password" name="password" class="form-control" required="required"></p>
                <p>Email<input type="email" name="email" class="form-control" required="required"></p>
                <input type="submit" name="formSubmit" value="Verify">
            </div>
        </div>
    </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitise_data($_POST['username']);
    $password = sanitise_data($_POST['password']);
    $email = sanitise_data($_POST['email']);

    $query = $conn->query("SELECT COUNT(*) as count FROM `Users` WHERE `Username` ='$username'");
    $row = $query->fetch();
    $count = $row[0];

    if ($count > 0) {
        $query = $conn->query("SELECT * FROM `Users` WHERE `Username`='$username'");
        $row = $query->fetch();
        if ($row[6] == 1) {
            if (password_verify($password, $row[4])) {
                // successful log on.
                $_SESSION["user_id"] = $row[0];
                $_SESSION["username"] = $row[1];
                $_SESSION["email"] = $row[2];
                $_SESSION['access_level'] = $row[5];
                $_SESSION["flash_message"] = "<div class='bg-success'>Login Successful</div>";
                header("Location:../../index.php");
            } else {
                // unsuccessful log on.
                $_SESSION["flash_message"] = "<div class='bg-warning'>Invalid Username or Password. <a href='../contactUs/contact.php'>Contact Us</a></div>";
                header('Location: '. $_SERVER['REQUEST_URI']);
            }
        } else {
            $_SESSION["flash_message"] = "<div class='bg-danger'>Account Disabled. <a href='../contactUs/contact.php'>Contact Us</a></div>";
            header('Location: '. $_SERVER['REQUEST_URI']);
        }
    } else {
        $_SESSION["flash_message"] = "<div class='bg-warning'>Invalid Username or Password. <a href='../contactUs/contact.php'>Contact Us</a></div>";
        header('Location: '. $_SERVER['REQUEST_URI']);
    }
}