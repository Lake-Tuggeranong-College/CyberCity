<?php include "template.php"; ?>

<title>Cyber City - login</title>


<h1 class='text-primary'>login</h1>


<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->


            <div class="col-md-12">
                <h2>Account Details</h2>
                <p>Please enter wanted username and password:</p>
                <p>User Name<input type="text" name="username" class="form-control" required="required"></p>
                <p>Password<input type="password" name="password" class="form-control" required="required"></p>


            </div>
        </div>
    </div>
    <input type="submit" name="formSubmit" value="Submit">
</form>




<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitise_data($_POST['username']);
    $password = sanitise_data($_POST['password']);

    $query = $conn->query("SELECT COUNT(*) as count FROM `user` WHERE `username`='$username'");
    $row = $query->fetch();
    $count = $row[0];

    if ($count > 0) {
        $query = $conn->query("SELECT * FROM `user` WHERE `username`='$username'");
        $row = $query->fetch();
        if (password_verify($password, $row[2])) {
            $_SESSION["user_id"] = $row[0];
            $_SESSION["username"] = $row[1];
            $_SESSION['access_level'] = $row[3];
            header("Location:index.php");
        }
        else {
            // unsuccessful log on.
            echo "<div class='alert alert-danger'>Invalid username or password</div>";
        }
    }
//    header("refresh: 3;");
}






