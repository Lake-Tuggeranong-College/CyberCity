<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(true, true, true)) {
    header("Location:../../index.php");
}

?>

<title>Register Page</title>


<h1 style = 'text-align: center'>User registration</h1>
<h2 style = 'text-align: center'>Create your account here</h2>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid centerBox">
        <div class="row">
            <!--Customer Details-->

            <div class="col-md-12">
                <h2>Account Details</h2>
                <p>Please enter a Username and Password:</p>
                <p>User Name<input type="text" name="username" class="form-control" required="required"></p>
                <p>Password<input type="password" name="password" class="form-control" required="required"></p>
                <input type="submit" name="formSubmit" value="Submit">
            </div>
        </div>
    </div>

</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitise_data($_POST['username']);
    $password = sanitise_data($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $accessLevel = 1;
    //$username;
    //$hashed_password;

// check username in database
    $query = $conn->query("SELECT COUNT(*) FROM Users WHERE Username='$username'");
    $data = $query->fetch();
    $numberOfUsers = (int)$data[0];

    if ($numberOfUsers > 0) {
        echo "This username has already been taken.";
    } else {
        $sql = "INSERT INTO Users (Username, HashedPassword, AccessLevel, Enabled) VALUES (:newUsername, :newPassword, :newAccessLevel, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newUsername', $username);
        $stmt->bindValue(':newPassword', $hashed_password);
        $stmt->bindValue(':newAccessLevel', $accessLevel);
        $stmt->execute();
        $_SESSION["flash_message"] = "Account Created!";
        header("Location:../../index.php");

    }

}
?>


