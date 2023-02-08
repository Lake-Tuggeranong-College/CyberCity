<?php include "template.php"; ?>
<title>Sign Up</title>

<h1 class='text-primary'>Sign Up Below</h1>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->

            <div class="col-md-12">
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
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //echo $username;
        //echo $hashed_password;

        $sql = "INSERT INTO users (Username, HashedPassword, AccessLevel) VALUES (:newUsername, :newPassword, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newUsername', $username);
        $stmt->bindValue(':newPassword', $hashed_password);
        $stmt->execute();
    }
?>