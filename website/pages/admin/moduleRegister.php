<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

?>

<title>Module Register page</title>

<h1>Please create your new Module & Challenge here</h1>
<h5 class='text'>Please complete the form below to make your new challenge and module</h5>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">

            <div class="col-12">


                <p>Module Location<input type="text" name="Location" class="form-control" required="required"></p>
                <p>Module Type <input type="text" name="Module" class="form-control" required="required"></p>
                <p>Module API key<input type="password" name="APIkey" class="form-control" required="required"></p>
                <p>Challenge Name<input type="text" name="challengeTitle" class="form-control" required="required"></p>
                <p>Challenge Description<input type="text" name="challengeDescription" class="form-control" required="required"></p>
                <p>Challenge Flag<input type="text" name="flag" class="form-control" required="required"></p>
                <p>Points Given<input type="text" name="pointsValue" class="form-control" required="required"></p>
            </div>
        </div>
    </div>
    <input type="submit" name="formSubmit" value="Submit">
</form>
</head>
<body>


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


