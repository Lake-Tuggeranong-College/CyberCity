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
                <span class="text-danger">stu-id@schoolsnet.act.edu.au <!-- TODO: SQL for this to dynamically fetch user's email --></span>
            </div>
        </div>
        
        <div class="col">
            <div class="p-3 py-5">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <h4>Edit Account</h4>
                </div>
                <form>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="labels" for="userName">Username</label>
                            <input type="text" class="form-control" id="userName" placeholder="Enter username here" value="">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="labels" for="email">Email ID</label>
                            <input type="text" class="form-control" id="email" placeholder="Enter e-mail here" value="">
                        </div>
                    <div class="mt-5 text-center">
                        <button class="btn btn-primary profile-button" type="button">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
