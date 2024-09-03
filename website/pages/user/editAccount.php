<?php
include_once "../../includes/template.php";

if (!authorisedAccess(true, false, false)) {
    header("Location:../../index.php");
}
?>

<div class="container rounded bg-dark bg-gradient mt-5 mb-5">
    <div class="row">
        <div class="col">
            <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                <!--
                        TODO:
                        - SQL for this to dynamically fetch user's profile picture.
                        - Maybe put this in a <form> tag so user can just click on the image to change, rather than clicking on the button.
                    -->
                <img class="rounded-circle mt-5" width="150px" src="" alt="placeholder">
                <!-- TODO: Hide this when player add their preferred profile picture up -->
                <input type="file" id="avatarInput" class="mt-3" accept="image/jpeg, image/jpg, image/png, image/webp, image/gif">
                <span class="text-warning font-weight-bold"><?= htmlspecialchars($_SESSION['username']); ?></span>
                <span class="text-danger"><?= htmlspecialchars($_SESSION['user_email']); ?> <!-- TODO: SQL for this to dynamically fetch user's email --></span>
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
                            <label class="labels" for="userName">Username</label>
                            <input type="text" class="form-control" id="userName" placeholder="Enter username here">
                        </div>

                        <div class="col-md-12">
                            <label class="labels" for="userEmail">Email ID</label>
                            <input type="text" class="form-control" id="userEmail" placeholder="Enter email here">
                        </div>

                        <div class="mt-5 text-center">
                            <button class="btn btn-primary profile-button" type="submit">Update Profile</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
