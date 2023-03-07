<?php include "template.php"; ?>
<title>Register Page</title>

<h1 class='text-primary'>Please register on our site</h1>
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
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //  echo $username;
    //   echo $hashed_password;

// check username in database
    $query = $conn->query("SELECT COUNT(*) FROM Users WHERE username='$username'");
    $data = $query->fetch();
    $numberOfUsers = (int)$data[0];

    if ($numberOfUsers > 0) {
        echo "This username has already been taken.";
    } else {
        $sql = "INSERT INTO Users (username, HashedPassword, AccessLevel) VALUES (:newUsername, :newPassword, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newUsername', $username);
        $stmt->bindValue(':newPassword', $hashed_password);
        $stmt->execute();
    }

}
?>

