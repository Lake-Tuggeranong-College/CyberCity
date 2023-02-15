<?php include "template.php"; ?>
<title>Sign In</title>
<h1 class='text-primary'>Sign In Below</h1>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" class="form-control" name="username">
    </div>
    <div class="form-group">
        <label for="pwd">Password:</label>
        <input type="password" class="form-control" name="pwd">
    </div>
    <input type="submit" name="formSubmit" value="Log In">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitise_data($_POST['username']);
    $password = sanitise_data($_POST['pwd']);

    $query = $conn->query("SELECT COUNT(*) as count FROM `users` WHERE `Username`='$username'");
    $row = $query->fetch();
    $count = $row[0];
    if ($count == 1) {
        $query = $conn->query("SELECT * FROM `users` WHERE `Username`='$username'");
        $row = $query->fetch();
        if(password_verify($password,$row[2])) {
            $_SESSION["user_id"] = $row[0];
            $_SESSION["username"] = $row[1];
            $_SESSION['access_level'] = $row[3];
            header("Location:index.php");
        } else {
            echo "<div class='alert alert-danger'>Invalid username or password</div>";
        }
    }


}
 ?>
</body>
</html>

