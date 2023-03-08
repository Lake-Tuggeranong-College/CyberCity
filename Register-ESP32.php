<?php include "template.php"; ?>
<title>ESP32 Register page</title>

<h1 class='text-primary'>Please enter new ESP32 details below</h1>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->

            <div class="col-md-12">
                <h2>ESP32 details</h2>
                <p>Please enter the new ESP32 loaction, Module name and APIkey:</p>
                <p>Location<input type="text" name="Location" class="form-control" required="required"></p>
                <p>Module<input type="text" name="Module" class="form-control" required="required"></p>
                <p>API key<input type="password" name="APIkey" class="form-control" required="required"></p>
            </div>
        </div>
    </div>
    <input type="submit" name="formSubmit" value="Submit">
</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //$username = sanitise_data($_POST['username']);
   // $password = sanitise_data($_POST['password']);
    $Location = ($_POST['Location']);
    $Module = ($_POST['Module']);
    $APIkey = ($_POST['APIkey']);
    $hashed_APIkey = password_hash($APIkey, PASSWORD_DEFAULT);
    echo  $Location;
    echo  $Module;
    echo  $hashed_APIkey;

// check Module and location in database
    $query = $conn->query("SELECT COUNT(*) FROM `Registered ESPs` WHERE Location='$Location'");
    $query = $conn->query("SELECT COUNT(*) FROM `Registered ESPs` WHERE Module='$Module'");
    $data = $query->fetch();
    $CheckModule = (int)$data[0];
    $CheckLoaction = (int)$data[0];

    if ($CheckModule > 0 && $CheckLoaction > 0) {
        echo "This Module is already in use at this location";
    } else {
        $sql = "INSERT INTO `Registered ESPs` (Location, Module, HashedAPIKey) VALUES (:newLocation, :newModule, :newHashedAPIkey)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':newLocation', $Location);
        $stmt->bindValue(':newModule', $Module);
        $stmt->bindValue(':newHashedAPIkey', $hashed_APIkey);
        $stmt->execute();
    }

}
?>


