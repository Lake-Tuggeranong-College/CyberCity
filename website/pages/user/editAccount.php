<?php
include_once "../../includes/template.php";

if (authorisedAccess(true, false, false)) {
    echo '<script type="text/javascript">location.replace("http://10.177.200.71/CyberCity/index.php")</script>';
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
<div class="container mt-5 mb-5">
    <div class="row g-4">
        <!-- Profile Section -->
        <div class="col-md-4 text-center">
            <div class="card bg-dark text-white p-4">
                <img src="path/to/profile.jpg" class="rounded-circle mx-auto" width="150" alt="Profile Picture">
                <input type="file" id="avatarInput" class="form-control mt-3" accept="image/*">
                <h5 class="mt-3"><?= htmlspecialchars($userData['Username']); ?></h5>
                <p><?= htmlspecialchars($userData['user_email']); ?></p>
            </div>
        </div>

        <!-- Edit Form Section -->
        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="mb-4 text-center">Edit Account</h4>
                <form action="editAccount.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= htmlspecialchars($userData['Username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($userData['user_email']); ?>" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
