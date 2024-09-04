    <?php include "../../includes/template.php";
    /** @var $conn */

    if (!authorisedAccess(true, true, true)) {
        header("Location:../../index.php");
    }

    ?>

    <title>Register Page</title>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div class="container-fluid centerBox">
            <div class="row">
                <!--Customer Details-->

                <div class="col-md-12">
                    <h2>Registration</h2>
                    <p>Please enter a Username and Password:</p>
                    <p>User Name<input type="text" name="username" class="form-control" required="required"></p>
                    <p>Password<input type="password" name="password" class="form-control" required="required"></p>
                    <p>Email<input type="email" name="email" class="form-control" required="required"></p>
                    <input type="submit" name="formSubmit" value="Register">
                </div>
            </div>
        </div>

    </form>

    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = sanitise_data($_POST['username']);
        $password = sanitise_data($_POST['password']);
        $email = sanitise_data($_POST['email']);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $accessLevel = 1;

    // check username in database
        $query = $conn->query("SELECT COUNT(*) FROM Users WHERE Username='$username'");
        $data = $query->fetch();
        $numberOfUsers = (int)$data[0];

        if ($numberOfUsers > 0) {
            echo "This username has already been taken.";
            header('Location: '. $_SERVER['REQUEST_URI']);
        } else {
            $sql = "INSERT INTO Users (Username, user_email, HashedPassword, AccessLevel, Enabled) VALUES (:newUsername, :newEmail, :newPassword, :newAccessLevel, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':newUsername', $username);
            $stmt->bindValue(':newEmail', $email);
            $stmt->bindValue(':newPassword', $hashed_password);
            $stmt->bindValue(':newAccessLevel', $accessLevel);
            $stmt->execute();

            $query = $conn->query("SELECT ID FROM Users WHERE Username='$username'");
            $data = $query->fetch();
            $UserID = $data["ID"];

            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;
            $_SESSION['access_level'] = '1';
            $_SESSION['user_id'] = $UserID;


            $_SESSION["flash_message"] = "<div class='bg-success'>Account Created!</div>";
            header("Location:../../index.php");

        }

    }
    ?>


