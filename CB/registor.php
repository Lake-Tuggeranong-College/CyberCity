<?php include "template.php"; ?>
    <title>Cyber City - registration</title>

    <h1 class='text-primary'>please register for our sight</h1>
    <! --
    create bootstrap form for 2 fields

    -user
    -pass
    data need cloceted and stored user databace acess level 1
    IMPORTANT - password be hashed prior ro save databace
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

    $query = $conn->query("SELECT COUNT(*) FROM users WHERE username='$username'")
    $data = $query->fetchAll();
    $numberOfUsers = (int)$data[0];

    if ($numberOfUsers > 0) {
        echo "this username has already been taken";
} else {

    }
    $sql = "INSERT INTO user (username, hashed_password, access_level) VALUES (:newUsername, :newPassword, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':newUsername', $username);
    $stmt->bindValue(':newPassword', $hashed_password);
    echo $hashed_password;
    echo $username;
    $stmt->execute();

}

?>