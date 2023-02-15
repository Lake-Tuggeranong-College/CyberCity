<?php include "template.php"; ?>
    <title>Cyber City - Registration</title>

    <h1 class='text-primary'>Please register for our site</h1>

    <!--
    Create a bootstrapped form for 2 fields
    - username
    - password
    data will be need collected, and stored in the 'user' table with an access level of 1.
    IMPORTANT - the password needs to be 'hashed' prior to saving to the database.
    -->

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
//    echo $username;
//    echo $hashed_password;


    // TODO CHECK IF USER EXISTS
    $query = $conn->query("SELECT COUNT(*) FROM user WHERE username='$username'");
    $data = $query->fetch();
    $numberOfUsers = (int)$data[0];

    if ($numberOfUsers > 0) {
        echo "This username has already been taken.";
    } else {
        $sql = "INSERT INTO user (username, hashed_password, access_level) VALUES (:newUsername, :newPassword, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newUsername', $username);
        $stmt->bindValue(':newPassword', $hashed_password);
        $stmt->execute();
    }


}


?>

</body>
</html>
