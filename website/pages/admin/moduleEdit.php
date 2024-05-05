<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

?>

<title>Module Edit page</title>


<?php
if (isset($_GET["ModuleID"])) {
    $moduleToLoad = $_GET["ModuleID"];
    $sql = $conn->query("SELECT * FROM RegisteredModules WHERE ID= " . $moduleToLoad);
    $moduleInformation = $sql->fetch();
    $moduleID = $moduleInformation["ID"];
    $moduleLocation = $moduleInformation["Location"];
    $moduleName = $moduleInformation["Module"];
    $moduleAPIKey = $moduleInformation["HashedAPIKey"];
    $moduleCurrentOutput = $moduleInformation["CurrentOutput"];
    $moduleEnabled = $moduleInformation["Enabled"];
} else {
    header("location:moduleList.php");
}
?>


<h1>Edit Module Information</h1>

<form action="moduleEdit.php?ModuleID=<?= $moduleToLoad ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->
            <div class="col-md-6">
                <h2>Module Details</h2>
                <p>Module Name<label>
                        <input type="text" name="moduleName" class="form-control" required="required"
                               value="<?= $moduleName ?>">
                    </label></p>
                <p>Module Location
                    <label>
                        <input type="text" name="moduleLocation" class="form-control" required="required"
                               value="<?= $moduleLocation ?>">
                    </label></p>
            </div>
            <div class="col-md-6">
                <h2>More Details</h2>
                <!--Product List-->
                <p>API Key
                    <label>
                        <input type="text" name="apiKey" class="form-control" required="required"
                               value="<?= $moduleAPIKey ?>">
                    </label></p>

                <p>Current Output
                    <label>
                        <input type="text" name="currentOutput" class="form-control" required="required"
                               value="<?= $moduleCurrentOutput ?>">
                    </label></p>
                <p>Enabled
                    <label>
                        <input type="text" name="Enabled" class="form-control" required="required"
                               value="<?= $moduleEnabled ?>">
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
    $newModule = sanitise_data($_POST["moduleName"]);
    $newLocation = sanitise_data($_POST["moduleLocation"]);
    $newOutput = sanitise_data($_POST["currentOutput"]);
    $newAPIKey = sanitise_data($_POST["apiKey"]);
    $newHashedAPIKey = password_hash($newAPIKey, PASSWORD_DEFAULT);
    $newEnabled = ($_POST["Enabled"]);
    $moduleToLoad = $_GET["ModuleID"];

    $sql = "UPDATE RegisteredModules SET Location= :newLocation, Module= :newModule, HashedAPIKey= :newHashedAPIkey, Enabled= :newEnabled, CurrentOutput=:newOutput WHERE ID ='$moduleToLoad'";
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




<?php
/*echo '<h2 class="text-danger">Debug Information. Comment out as necessary</h2><pre>';
print_r($moduleInformation);
echo '</pre>';


*/ ?>


