<?php
include_once "../../includes/template.php";

if (!authorisedAccess(true, false, false)) {
    header("Location:../../index.php");
    exit(); // clear one-time usage resource as best practice
}

// Fetch the user's current data from the database
$userId = $_SESSION['user_id'];
$query = "SELECT Username, user_email FROM Users WHERE ID = :userId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);   
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitise the input data
    $username = sanitise_data($_POST['username']);
    $email = sanitise_data($_POST['email']);

	// Update the user's data in the database
    $query = "UPDATE Users SET Username = :newUsername, user_email = :newEmail WHERE ID = :userId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':newUsername', $username);
    $stmt->bindParam(':newEmail', $email);
    $stmt->bindParam(':userId', $userId);
	$stmt->execute();
	$userData = $stmt->fetch(PDO::FETCH_ASSOC);

	// Update the session data with new changes
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Flash message to indicate that the changes are done successfully
    $_SESSION["flash_message"] = "<div class='bg-success'>Account Updated!</div>";

    // Redirect to this page
	header("Location:../../index.php");
	exit(); // clear one-time usage resource as best practice
}
?>

<div class="container rounded bg-dark bg-gradient mt-5 mb-5">
    <div class="row">
        <div class="col">
            <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                <!-- TODO: SQL for this to dynamically fetch user's profile picture. -->
                <img class="rounded-circle mt-5" width="150px" src="" alt="placeholder">
                <!-- TODO: Maybe put this in a <form> tag so user can just click on the image to change, rather than clicking on the button. -->
                <input type="file" id="avatarInput" class="mt-3" accept="image/jpeg, image/jpg, image/png, image/webp, image/gif">
                <span class="text-warning font-weight-bold"><?= htmlspecialchars($userData['Username']); ?></span>
                <span class="text-danger"><?= htmlspecialchars($userData['user_email']); ?></span>
            </div>
        </div>

        <div class="col">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <h4>Edit Account</h4>
                </div>

                <form action="editAccount.php" method="post">
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="labels" for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($userData['Username']); ?>" placeholder="Enter username here">
                        </div>

                        <div class="col-md-12">
                            <label class="labels" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userData['user_email']); ?>" placeholder="Enter email here">
                        </div>

                        <div class="mt-5 text-center">
                            <button class="btn btn-primary profile-button" type="submit">Update Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>