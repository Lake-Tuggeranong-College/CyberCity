<?php include "template.php";
/** @var $conn */ ?>

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
} else {
    header("location:moduleList.php");
}
?>


<h1 class='text-primary'>Edit Module Information</h1>

<form action="moduleEdit.php?ModuleID=<?= $moduleToLoad ?>" method="post" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <!--Customer Details-->
            <div class="col-md-6">
                <h2>Module Details</h2>
                <p>Module Name<input type="text" name="moduleName" class="form-control" required="required"
                                     value="<?= $moduleName ?>"></p>
                <p>Module Location
                    <input type="text" name="moduleLocation" class="form-control" required="required"
                           value="<?= $moduleLocation ?>"></p>
                </p>
            </div>
            <div class="col-md-6">
                <h2>More Details</h2>
                <!--Product List-->
                <p>API Key
                    <input type="text" name="apiKey" class="form-control" required="required"
                           value="<?= $moduleAPIKey ?>"></p>
                </p>

                <p>Current Output
                    <input type="text" name="currentOutput" class="form-control" required="required"
                           value="<?= $moduleCurrentOutput ?>"></p>
                </p>

            </div>
        </div>
    </div>
    <input type="submit" name="formSubmit" value="Update">
</form>


<!-- If the user presses update, this code runs-->

<?php
// Back End
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = sanitise_data($_POST["moduleName"]);
    $newLocation = sanitise_data($_POST["moduleLocation"]);
    $newOutput = sanitise_data($_POST["currentOutput"]);
    $newAPIKey = sanitise_data($_POST["apiKey"]);

    $newHashedAPIKey = password_hash($newAPIKey, PASSWORD_DEFAULT);

    $sql = "UPDATE RegisteredModules SET Location=?, Module=?, HashedAPIKey=?, CurrentOutput=? WHERE ID=?";
    $sqlStmt = $conn->prepare($sql);
    $sqlStmt->execute([$newLocation, $newName, $newHashedAPIKey, $newOutput, $moduleID]);


}

?>


<?= outputFooter() ?>

<?php
echo '<h2 class="text-danger">Debug Information. Comment out as necessary</h2><pre>';
print_r($moduleInformation);
echo '</pre>';


?>


</html>
</head>
