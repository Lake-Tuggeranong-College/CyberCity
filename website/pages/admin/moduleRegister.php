<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <title>Module Register Page</title>

    <h1 class="d-flex justify-content-center my-3 text-danger">Module Registering Form</h1>

    <div class="container-fluid">
        <div class="row g-3">

            <!-- Module location input form -->
            <div class="col-md-6">
                <label for="inputModuleLocation" class="form-label fw-bold text-warning">Module location</label>
                <input type="text" class="form-control" name='Location' id="inputModuleLocation" placeholder="Type in the module location here" required>
            </div>

            <!-- Module type input form -->
            <div class="col-md-6">
                <label for="inputModuleType" class="form-label fw-bold text-warning">Module type</label>
                <input type="text" class="form-control" name="Module" id="inputModuleType" placeholder="Type in the module type here" required>
            </div>

            <!-- Module API key input form -->
            <div class="col-md-6">
                <label for="inputModuleApiKey" class="form-label fw-bold text-warning">Module API key</label>
                <input type="text" class="form-control" name="APIKey" id="inputModuleApiKey" placeholder="Type in the module API key here" required>
            </div>

            <!-- Challenge name input form -->
            <div class="col-md-6">
                <label for="inputChallengeName" class="form-label fw-bold text-warning">Challenge name</label>
                <input type="text" class="form-control" id="inputChallengeName" placeholder="Type in the challenge name here" required>
            </div>
            
            <!-- Challenge description input form -->
            <div class="col-12">
                <label for="inputChallengeDescription" class="form-label fw-bold text-warning">Challenge description</label>
                <input type="text" class="form-control" id="inputChallengeDescription" placeholder="Type in the challenge description here" required>
            </div>
            
            <!-- Challenge flag input form -->
            <div class="col-md-6">
                <label for="inputChallengeFlag" class="form-label fw-bold text-warning">Challenge flag</label>
                <input type="text" class="form-control" id="inputChallengeFlag" placeholder="Type in the challenge flag here" required>
            </div>
            
            <!-- Point given input form -->
            <div class="col-md-6">
                <label for="inputPointGiven" class="form-label fw-bold text-warning">Point given</label>
                <input type="text" class="form-control" id="inputPointGiven" placeholder="Type in the point given here" required>
            </div>

            <!-- Submit buttom form -->
            <div class="col-12 d-flex justify-content-center">
                <button type="submit" class="btn btn-primary fw-bold">Submit</button>
            </div>
        </div>
    </div>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") { //Make Module Entry
    $location = ($_POST['Location']);
    $module = ($_POST['Module']);
    $APIkey = ($_POST['APIkey']);


    $hashed_APIkey = password_hash($APIkey, PASSWORD_DEFAULT);
    //echo  $location;
    //echo  $module;
    //echo  $hashed_APIkey;

// check Module and location in database
    $query = $conn->query("SELECT COUNT(*) FROM `RegisteredModules` WHERE Location='$location'");
    $query2 = $conn->query("SELECT COUNT(*) FROM `RegisteredModules` WHERE Module='$module'");
    $data = $query->fetch();
    $data2 = $query2->fetch();
    $checkModule = (int)$data[0];
    $checkLocation = (int)$data2[0];

    if ($checkModule > 0 && $checkLocation > 0) {
        echo "This Module is already in use at this location";
    } else {
        $sql = "INSERT INTO `RegisteredModules` (Location, Module, HashedAPIKey, Enabled, Registered_date) VALUES (:newLocation, :newModule, :newHashedAPIkey, :enabled, :current_date)";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newLocation', $location);
        $stmt->bindValue(':newModule', $module);
        $stmt->bindValue(':newHashedAPIkey', $hashed_APIkey);
        $stmt->bindValue(':enabled', 1);
        $stmt->bindValue(':current_date', date("F d Y H:i:s."));
        $stmt->execute();
        //$_SESSION["flash_message"] = "Module Created";
        //header("Location:index.php");
    }
    $query = $conn->query("SELECT `ID` FROM `RegisteredModules` WHERE Location='$location' AND Module='$module'");
    $data = $query->fetch();
    $moduleID = $data[0];
    $flag = sanitise_data($_POST['flag']);
    $hashed_flag = password_hash($flag, PASSWORD_DEFAULT);
    $points = sanitise_data($_POST['pointsValue']);
    $title = sanitise_data($_POST['challengeTitle']);
    $disc = sanitise_data($_POST['challengeDescription']);
    $flagList = $conn->query("SELECT HashedFlag FROM Challenges");
    $sql = "INSERT INTO Challenges (HashedFlag, PointsValue,challengeTitle,challengeText,moduleID) VALUES (:newFlag, :newPoints,:newTitle,:newText, :moduleID)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':newFlag', $hashed_flag);
    $stmt->bindValue(':newPoints', $points);
    $stmt->bindValue(':newTitle', $title);
    $stmt->bindValue(':newText', $disc);
    $stmt->bindValue(':moduleID', $moduleID);
    $stmt->execute();
    echo "Flag Made";
}
?>


