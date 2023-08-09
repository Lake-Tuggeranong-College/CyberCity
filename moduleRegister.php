<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>

<title>Module Register page</title>

<h1 class='text-primary'>Please create your new challenge here</h1>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->

            <div class="col-md-12">
                <h2>challenge details</h2>
                <p>Please enter the new Module location, Module name and APIkey, the name of the flag, points given by the flag:</p>
                <p>Location<input type="text" name="Location" class="form-control" required="required"></p>
                <p>Module<input type="text" name="Module" class="form-control" required="required"></p>
                <p>API key<input type="password" name="APIkey" class="form-control" required="required"></p>
                <p>Name<input type="text" name="challengeTitle" class="form-control" required="required"></p>
                <p>Description<input type="text" name="challengeDescription" class="form-control" required="required"></p>
                <p>Flag<input type="text" name="flag" class="form-control" required="required"></p>
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
        $sql = "INSERT INTO `RegisteredModules` (Location, Module, HashedAPIKey, Enabled) VALUES (:newLocation, :newModule, :newHashedAPIkey, :enabled)";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newLocation', $location);
        $stmt->bindValue(':newModule', $module);
        $stmt->bindValue(':newHashedAPIkey', $hashed_APIkey);
        $stmt->bindValue(':enabled', 1);
        $stmt->execute();
        //$_SESSION["flash_message"] = "Module Created";
        //header("Location:index.php");
    }

}
if ($_SERVER["REQUEST_METHOD"] == "POST") { //Make Challange Entry
    $flag = sanitise_data($_POST['flag']);
    $hashed_flag = password_hash($flag, PASSWORD_DEFAULT);
    $points = sanitise_data($_POST['pointsValue']);
    $title = sanitise_data($_POST['challengeTitle']);
    $disc = sanitise_data($_POST['challengeDescription']);
    $flagList = $conn->query("SELECT HashedFlag FROM Challenges");
    $sql = "INSERT INTO Challanges (HashedFlag, PointsValue,challangeTitle,challangeText,moduleID) VALUES (:newFlag, :newPoints,:newTitle,:newText)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':newFlag', $hashed_flag);
    $stmt->bindValue(':newPoints', $points);
    $stmt->bindValue(':newTitle', $title);
    $stmt->bindValue(':newText', $disc);
    $stmt->execute();
    echo "Flag Made";

}
?>

<?php echo outputFooter(); ?>
