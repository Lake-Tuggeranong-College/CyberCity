<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>

<title>User Edit page</title>


<?php
if (isset($_GET["UserID"])) {
    $userToLoad = $_GET["UserID"];
    $sql = $conn->query("SELECT * FROM Users WHERE ID= " . $userToLoad);
    $userInformation = $sql->fetch();
    $userID = $userInformation["ID"];
    $userName = $userInformation["Username"];
    $userAccessLevel = $userInformation["AccessLevel"];
    $userEnabled = $userInformation["Enabled"];
    $userScore = $userInformation["Score"];
} else {
    header("location:moduleList.php");
}
?>


<h1 class='text-primary'>Edit Module Information</h1>

<form action="userEdit.php?UserID=<?= $userToLoad ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->
            <div class="col-md-6">
                <h2>Module Details</h2>
                <p>Module Name<label>
                        <input type="text" name="userName" class="form-control" required="required"
                               value="<?= $userName ?>">
                    </label></p>
                <p>Module Location
                    <label>
                        <input type="password" name="password" class="form-control" required="required"
                               value="<?= $userID ?>">
                    </label></p>
            </div>
            <div class="col-md-6">
                <h2>More Details</h2>
                <!--Product List-->
                <p>API Key
                    <label>
                        <input type="text" name="AccessLevel" class="form-control" required="required"
                               value="<?= $userAccessLevel ?>">
                    </label></p>

                <p>Current Output
                    <label>
                        <input type="text" name="Enabled" class="form-control" required="required"
                               value="<?= $userEnabled ?>">
                    </label></p>
                <p>Current Score
                    <label>
                        <input type="text" name="Score" class="form-control" required="required"
                               value="<?= $userScore ?>">
                    </label></p>

            </div>
        </div>
    </div>
    <input type="submit" name="formSubmit" value="Update">
</form>


<!-- If the user presses update, this code runs-->

<?php
// Back End
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newModule = sanitise_data($_POST["userName"]);
    $newLocation = sanitise_data($_POST["moduleLocation"]);
    $newOutput = sanitise_data($_POST["currentOutput"]);
    $newAPIKey = sanitise_data($_POST["apiKey"]);
    $newHashedAPIKey = password_hash($newAPIKey, PASSWORD_DEFAULT);
    $userToLoad = $_GET["ModuleID"];

    $sql = "UPDATE Users SET Username= :newusername, HashedPassword= :newpassword, AccessLevel= :newaccesslevel, Enabled= :newEnabled, Score=:newscore WHERE ID ='$userToLoad'";
    //$sql = "INSERT INTO `RegisteredModules` (Location, Module, HashedAPIKey, Enabled) VALUES (:newLocation, :newModule, :newHashedAPIkey, :enabled)";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":newModule", $newModule);
    $stmt->bindValue(":newLocation", $newLocation);
    $stmt->bindValue(":newOutput", $newOutput);
    $stmt->bindValue(":newHashedAPIkey", $newHashedAPIKey);
    $stmt->bindValue(":newEnabled", $newEnabled);

    $stmt->execute();

//    header("location:moduleInformation.php?ModuleID=$moduleToLoad");

}
?>


<?= outputFooter() ?>

<?php
/*echo '<h2 class="text-danger">Debug Information. Comment out as necessary</h2><pre>';
print_r($moduleInformation);
echo '</pre>';


*/ ?>


