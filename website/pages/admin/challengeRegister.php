<?php include "../../includes/template.php";
/** @var $conn */
echo BASE_URL;
if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}
?>
<h2 class="mt-5">Register New Challenge</h2>
<?php



if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // Check if the image file is set and handle the upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $targetDir = BASE_URL."assets/img/challengeImages";
        $targetFile = basename($_FILES["image"]["name"]);

        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "<div class='alert alert-danger'>File is not an image.</div>";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($targetFile)) {
            echo "<div class='alert alert-danger'>Sorry, file already exists.</div>";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            echo "<div class='alert alert-danger'>Sorry, your file is too large.</div>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "<div class='alert alert-danger'>Sorry, your file was not uploaded.</div>";
            // if everything is ok, try to upload file
        } else {
            $finalDir = "/var/www/".$targetDir."/".$targetFile;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $finalDir)) {
                echo "<div class='alert alert-success'>The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.</div>";

                // Insert new challenge
                $challengeTitle = $_POST["challengeTitle"];
                $challengeText = $_POST["challengeText"];
                $flag = $_POST["flag"];
                $pointsValue = $_POST["pointsValue"];
                $moduleName = $_POST["moduleName"];
                $moduleValue = $_POST["moduleValue"];
                $dockerChallengeID = !empty($_POST["dockerChallengeID"]) ? $_POST["dockerChallengeID"] : null;
                $container = !empty($_POST["container"]) ? $_POST["container"] : null;
                $image = $targetFile;
                $enabled = $_POST["enabled"];
                $categoryID = $_POST["categoryID"];
                $projectID = $_POST["project_id"];

                $insertSql = "INSERT INTO Challenges (challengeTitle, challengeText, flag, pointsValue, moduleName, moduleValue, dockerChallengeID, container, Image, Enabled, categoryID) 
                                      VALUES (:challengeTitle, :challengeText, :flag, :pointsValue, :moduleName, :moduleValue, :dockerChallengeID, :container, :image, :enabled, :categoryID)";

                $stmt = $conn->prepare($insertSql);
                $stmt->bindParam(':challengeTitle', $challengeTitle, PDO::PARAM_STR);
                $stmt->bindParam(':challengeText', $challengeText, PDO::PARAM_STR);
                $stmt->bindParam(':flag', $flag, PDO::PARAM_STR);
                $stmt->bindParam(':pointsValue', $pointsValue, PDO::PARAM_INT);
                $stmt->bindParam(':moduleName', $moduleName, PDO::PARAM_STR);
                $stmt->bindParam(':moduleValue', $moduleValue, PDO::PARAM_STR);
                $stmt->bindParam(':dockerChallengeID', $dockerChallengeID, PDO::PARAM_STR);
                $stmt->bindParam(':container', $container, PDO::PARAM_INT);
                $stmt->bindParam(':image', $image, PDO::PARAM_STR);
                $stmt->bindParam(':enabled', $enabled, PDO::PARAM_INT);
                $stmt->bindParam(':categoryID', $categoryID, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // Get the last inserted challenge ID
                    $challengeID = $conn->lastInsertId();

                    // Insert into ProjectChallenges table

                    $insertProjectChallengeSql = "INSERT INTO ProjectChallenges (challenge_id, project_id) VALUES (:challenge_id, :project_id)";
                    $stmtProjectChallenge = $conn->prepare($insertProjectChallengeSql);
                    $stmtProjectChallenge->bindParam(':challenge_id', $challengeID, PDO::PARAM_INT);
                    $stmtProjectChallenge->bindParam(':project_id', $projectID, PDO::PARAM_INT);

                    if ($stmtProjectChallenge->execute()) {
                        echo "<div class='alert alert-success'>Challenge registered and linked to project successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error linking challenge to project.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Error registering challenge.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>No file was uploaded or there was an error uploading the file.</div>";
    }


//    if ($stmt->execute()) {
//    } else {
//    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
}
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Register Challenge</h4>
        </div>
        <div class="card-body">
            <form method="post" action="" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="challengeTitle" class="form-label">Challenge Title</label>
                        <input type="text" class="form-control" id="challengeTitle" name="challengeTitle" required>
                    </div>
                    <div class="col-md-6">
                        <label for="flag" class="form-label">Flag</label>
                        <input type="text" class="form-control" id="flag" name="flag" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="challengeText" class="form-label">Challenge Text</label>
                    <textarea class="form-control" id="challengeText" name="challengeText" rows="4" required></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="pointsValue" class="form-label">Points Value</label>
                        <input type="number" class="form-control" id="pointsValue" name="pointsValue" required>
                    </div>
                    <div class="col-md-4">
                        <label for="container" class="form-label">Container</label>
                        <input type="number" class="form-control" id="container" name="container" required>
                    </div>
                    <div class="col-md-4">
                        <label for="enabled" class="form-label">Enabled</label>
                        <input type="number" class="form-control" id="enabled" name="enabled" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="moduleName" class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="moduleName" name="moduleName" required>
                    </div>
                    <div class="col-md-6">
                        <label for="moduleValue" class="form-label">Module Value</label>
                        <input type="text" class="form-control" id="moduleValue" name="moduleValue" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dockerChallengeID" class="form-label">Docker Challenge ID</label>
                        <input type="text" class="form-control" id="dockerChallengeID" name="dockerChallengeID"
                               required>
                    </div>
                    <div class="col-md-6">
                        <label for="categoryID" class="form-label">Category ID</label>
                        <input type="number" class="form-control" id="categoryID" name="categoryID" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">

                        <label for="projectID" class="form-label">Select Project</label>
                        <!-- These options should be populated dynamically from the Projects table -->
                        <select class="form-select" id="projectID" name="projectID" required>

                            <?php
                            $projectList = $conn->query("SELECT project_id, project_name FROM CyberCity.Projects");
                            while ($row = $projectList->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row['project_id']) . '">' . htmlspecialchars($row['project_name']) . '</option>';
                            }
                            ?>
                        </select>

                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image Upload</label>
                    <input type="file" class="form-control" id="image" name="image" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register Challenge</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
