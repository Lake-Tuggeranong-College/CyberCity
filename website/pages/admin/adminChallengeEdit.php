<?php include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

// Back End
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update challenge information
    $challengeID = $_POST["challengeID"];
    $challengeTitle = $_POST["challengeTitle"];
    $challengeText = $_POST["challengeText"];
    $challengeFlag = $_POST["challengeFlag"];
    $challengePoints = $_POST["challengePoints"];
    $challengeName = $_POST["challengeName"];
    $challengeValue = $_POST["challengeValue"];
    $challengeDockerID = $_POST["challengeDockerID"];
    $challengeContainer = $_POST["challengeContainer"];
    $challengeCategory = $_POST["challengeCategory"];
    $challengeEnabled = $_POST["challengeEnabled"];

    $updateSql = "UPDATE Challenges SET 
                challengeTitle = :challengeTitle,
                challengeText = :challengeText,
                flag = :challengeFlag,
                pointsValue = :challengePoints,
                moduleName = :challengeName,
                moduleValue = :challengeValue,
                dockerChallengeID = :challengeDockerID,
                container = :challengeContainer,
                categoryID = :challengeCategory,
                Enabled = :challengeEnabled
                WHERE ID = :challengeID";

    $stmt = $conn->prepare($updateSql);
    $stmt->bindParam(':challengeID', $challengeID, PDO::PARAM_INT);
    $stmt->bindParam(':challengeTitle', $challengeTitle, PDO::PARAM_STR);
    $stmt->bindParam(':challengeText', $challengeText, PDO::PARAM_STR);
    $stmt->bindParam(':challengeFlag', $challengeFlag, PDO::PARAM_STR);
    $stmt->bindParam(':challengePoints', $challengePoints, PDO::PARAM_INT);
    $stmt->bindParam(':challengeName', $challengeName, PDO::PARAM_STR);
    $stmt->bindParam(':challengeValue', $challengeValue, PDO::PARAM_STR);
    $stmt->bindParam(':challengeDockerID', $challengeDockerID, PDO::PARAM_STR);
    $stmt->bindParam(':challengeContainer', $challengeContainer, PDO::PARAM_INT);
    $stmt->bindParam(':challengeCategory', $challengeCategory, PDO::PARAM_INT);
    $stmt->bindParam(':challengeEnabled', $challengeEnabled, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION["flash_message"] = "<div class='bg-success'>Updated</div>";
    } else {
        $_SESSION["flash_message"] = "<div class='bg-success'>Error</div>";
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);

}
?>
<title>Challenge Edit page</title>


<?php
if (isset($_GET["ChallengeID"])) {
    $challengeToLoad = $_GET["ChallengeID"];
    $sql = $conn->query("SELECT * FROM Challenges WHERE ID= " . $challengeToLoad);
    $challengeInformation = $sql->fetch();

    // Loading to set data
//    $challengeID = $challengeInformation["ID"];
//    $challengeTitle = $challengeInformation["challengeTitle"];
//    $challengeText = $challengeInformation["challengeText"];
//    $challengeFlag = $challengeInformation["flag"];
//    $challengePoints = $challengeInformation["pointsValue"];
//    $moduleName = $challengeInformation["moduleName"];
//    $moduleValue = $challengeInformation["moduleValue"];
//    $challengeDockerID = $challengeInformation["dockerChallengeID"];
//    $challengeContainer = $challengeInformation["container"];
//    $challengeCategory = $challengeInformation["categoryID"];
//    $challengeEnabled = $challengeInformation["Enabled"];

} else {
    header("location:adminChallengeList.php");
}
?>


<form action="./adminChallengeEdit.php?ChallengeID=<?= $challengeToLoad ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="challengeID" value="<?php echo $challengeInformation['ID']; ?>">
    <div class="form-group">
        <label for="challengeTitle">Challenge Title</label>
        <input type="text" class="form-control" id="challengeTitle" name="challengeTitle"
               value="<?php echo $challengeInformation['challengeTitle']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengeText">Challenge Text</label>
        <textarea class="form-control" id="challengeText" name="challengeText" rows="3"
                  required><?php echo $challengeInformation['challengeText']; ?></textarea>
    </div>
    <div class="form-group">
        <label for="challengeFlag">Challenge Flag</label>
        <input type="text" class="form-control" id="challengeFlag" name="challengeFlag"
               value="<?php echo $challengeInformation['flag']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengePoints">Challenge Points</label>
        <input type="number" class="form-control" id="challengePoints" name="challengePoints"
               value="<?php echo $challengeInformation['pointsValue']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengeName">Challenge Name</label>
        <input type="text" class="form-control" id="challengeName" name="challengeName"
               value="<?php echo $challengeInformation['moduleName']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengeValue">Challenge Value</label>
        <input type="text" class="form-control" id="challengeValue" name="challengeValue"
               value="<?php echo $challengeInformation['moduleValue']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengeDockerID">Challenge Docker ID</label>
        <input type="text" class="form-control" id="challengeDockerID" name="challengeDockerID"
               value="<?php echo $challengeInformation['dockerChallengeID']; ?>">
    </div>
    <div class="form-group">
        <label for="challengeContainer">Challenge Container</label>
        <input type="number" class="form-control" id="challengeContainer" name="challengeContainer"
               value="<?php echo $challengeInformation['container']; ?>">
    </div>
    <div class="form-group">
        <label for="challengeCategory">Challenge Category</label>
        <input type="number" class="form-control" id="challengeCategory" name="challengeCategory"
               value="<?php echo $challengeInformation['categoryID']; ?>" required>
    </div>
    <div class="form-group">
        <label for="challengeEnabled">Challenge Enabled</label>
        <input type="number" class="form-control" id="challengeEnabled" name="challengeEnabled"
               value="<?php echo $challengeInformation['Enabled']; ?>" required>
    </div>
    <button type="submit" name="formSubmit" class="btn btn-primary">Update Challenge</button>
</form>
</div>

