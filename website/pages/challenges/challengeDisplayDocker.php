
<?php
include "../../includes/template.php";
$challengeToLoad = $_GET["challengeID"] ?? -1;

if (!authorisedAccess(false, true, true)) {
    header("Location:../../index.php");
    exit;
}

$sql = $conn->query("SELECT ID, moduleName, challengeTitle, challengeText, pointsValue, flag, dockerChallengeID FROM Challenges WHERE ID = $challengeToLoad ORDER BY ID DESC");
$result = $sql->fetch();

$challengeID = $result["ID"];
$moduleName = $result["moduleName"];
$title = $result["challengeTitle"];
$challengeText = $result["challengeText"];
$pointsValue = $result["pointsValue"];
$flag = $result["flag"];
$dChallengeID = $result["dockerChallengeID"];

$user = $_SESSION["user_id"];
$containerQuery = $conn->query("SELECT timeInitialised, port FROM DockerContainers WHERE userID = '$user'");
$containerData = $containerQuery->fetch();

if ($containerQuery->rowCount() != 0) {
    $timeInitialised = $containerData["timeInitialised"];
    $port = $containerData["port"];
    $timestamp = strtotime($timeInitialised) + 1200;
    $deletionTime = date('G:i', $timestamp);
}

$moduleQuery = $conn->query("SELECT Image FROM Challenges WHERE ID = $challengeToLoad");
$moduleInformation = $moduleQuery->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEnteredFlag = sanitise_data($_POST['hiddenflag']);
    if ($userEnteredFlag == $flag) {
        $query = $conn->query("SELECT * FROM UserChallenges WHERE challengeID ='$challengeID' AND userID = '$user'");
        if ($query->rowCount() > 0) {
            $_SESSION["flash_message"] = "<div class='bg-warning'>Flag Success! Challenge already completed, no points awarded</div>";
        } else {
            $conn->prepare("INSERT INTO UserChallenges (userID, challengeID) VALUES ('$user', '$challengeID')")->execute();
            $conn->prepare("UPDATE Users SET Score = Score + '$pointsValue' WHERE ID='$user'")->execute();
            $_SESSION["flash_message"] = "<div class='bg-success'>Success!</div>";
        }
        header("Location:./challengesList.php");
        exit;
    } else {
        $_SESSION["flash_message"] = "<div class='bg-danger'>Flag failed - Try again</div>";
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Challenge Information</title>
</head>
<body>
<header class="container-fluid d-flex align-items-center justify-content-center">
    <h1 class="text-uppercase">Challenge - <?= $title ?></h1>
</header>
<section class="pt-4 pd-2" style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border border-dark-subtle border-2">
            <div class="col-2 border-start border-end border-dark-subtle border-2">Challenge Image</div>
            <div class="col-2 border-start border-end border-dark-subtle border-2">Challenge Name</div>
            <div class="col-7 border-start border-end border-dark-subtle border-2">Challenge Description</div>
            <div class="col-1 border-start border-end border-dark-subtle border-2">Challenge Points</div>
        </div>
        <div class="row border border-top-0 border-dark-subtle border-2">
            <div class="col-2 border-start border-end border-dark-subtle border-2">
                <?php if ($moduleInformation['Image']): ?>
                    <div class='image'><img
                                src='<?= BASE_URL ?>assets/img/challengeImages/<?= $moduleInformation['Image'] ?>'
                                width='100' height='100'></div>
                <?php else: ?>
                    <div class='image'><img src='<?= BASE_URL ?>assets/img/challengeImages/Image Not Found.jpg'
                                            width='100' height='100'></div>
                <?php endif; ?>
            </div>
            <div class="col-2 fw-bold d-flex align-items-center justify-content-center"><?= $title ?></div>
            <div class="col-7 border-start border-end border-dark-subtle border-2"><?= $challengeText ?></div>
            <div class="col-1 d-flex align-items-center justify-content-center"><?= $pointsValue ?></div>
        </div>
        <div class="row border border-top-0 border-dark-subtle border-2">
            <p class="text-success fw-bold pt-3">Good luck and have fun!</p>
        </div>
        <hr style="border: none; position: relative; margin: 1.5rem 0; height: 4px; color: red; background-color: red;">
        <form action="challengeDisplay.php?moduleID=<?= $moduleName ?>" method="post" enctype="multipart/form-data">
            <div class="form-floating">
                <input type="text" class="flag-input" id="flag" name="hiddenflag" placeholder="CTF{Flag_Here}">
                <p id="functionAssistant" class="form-text text-start font-size-sm">You'll have to hit the "Enter" key
                    when finish entering the hidden flag.</p>
            </div>
        </form>
    </div>
</section>
<section class="pt-4 pd-2" style="padding: 10px;">
    <div class="container-fluid text-center">
        <div class="row border border-dark-subtle border-2">
            <div class="col border-start border-end border-dark-subtle border-2">Container Information</div>
            <div class="col border-start border-end border-dark-subtle border-2">Container Controls</div>
            <div class="col border-start border-end border-dark-subtle border-2">Deletion Time</div>
        </div>
        <div class="row border border-top-0 border-dark-subtle border-2">
            <div class="col border-start border-end border-dark-subtle border-2">
                <?= $containerQuery->rowCount() == 1 ? "IP: 10.177.200.71, Port: $port" : "Container not initialised" ?>
            </div>
            <div class="col border-start border-end border-dark-subtle border-2">
                <button id="startContainerButton" onclick="startContainer()"
                        class="btn <?= $containerQuery->rowCount() != 0 ? 'disabled btn-outline-success' : 'btn-success' ?>">
                    Start Container
                </button>
                <button id="startContainerButton" onclick="stopContainer()"
                        class="btn <?= $containerQuery->rowCount() == 0 ? 'disabled btn-outline-danger' : 'btn-danger' ?>">
                    Stop Container
                </button>
                <button id="startContainerButton" onclick="addTime()"
                        class="btn <?= $containerQuery->rowCount() == 0 ? 'disabled btn-outline-warning' : 'btn-warning' ?>">
                    Add Time
                </button>
            </div>
            <div class="col border-start border-end border-dark-subtle border-2">
                <?= $containerQuery->rowCount() == 1 ? "Shutdown time: $deletionTime" : "Container not initialised" ?>
            </div>
        </div>
        <hr style="border: none; position: relative; margin: 1.5rem 0; height: 4px; color: red; background-color: red;">
    </div>
</section>
<footer style="padding: 10px;">
    <h2 class='ps-3'>Recent Data</h2>
    <div class="container-fluid">
        <div class="row border text-center">
            <div class="col border-end">Data & Time</div>
            <div class="col">Data</div>
        </div>
        <?php
        $sql = $conn->query("SELECT * FROM ModuleData WHERE moduleID = $challengeToLoad ORDER BY id DESC LIMIT " . ($moduleName == 43 ? 10 : 5));
        while ($moduleIndividualData = $sql->fetch()) {
            echo "<div class='row border border-top-0'>";
            echo "<div class='col border-end text-center'>" . $moduleIndividualData["DateTime"] . "</div>";
            echo "<div class='col text-center'>" . $moduleIndividualData["Data"] . "</div>";
            echo "</div>";
        }
        ?>
    </div>
</footer>
<script type="text/javascript">
    function startContainer() {
        axios.post('<?= BASE_URL ?>pages/challenges/docker/startContainer.php', new URLSearchParams({
            dChallengeID: '<?=$dChallengeID?>',
            userID: '<?=$user?>',
        })).then(response => {
            console.log('Response:', response.data);
        }).catch(error => {
            console.error('Error:', error);
        });
        setTimeout(() => location.reload(), 1000);
    }


    function addTime() {
        axios.post('<?= BASE_URL ?>pages/challenges/docker/addTime.php', new URLSearchParams({
            dChallengeID: '<?=$dChallengeID?>',
            userID: '<?=$user?>',
        })).then(response => {
            console.log('Response:', response.data);
        }).catch(error => {
            console.error('Error:', error);
        });
        setTimeout(() => location.reload(), 1000);
    }


    function stopContainer() {
        axios.post('<?= BASE_URL ?>pages/challenges/docker/stopContainer.php', new URLSearchParams({
            dChallengeID: '<?=$dChallengeID?>',
            userID: '<?=$user?>',
        })).then(response => {
            console.log('Response:', response.data);
        }).catch(error => {
            console.error('Error:', error);
        });
        setTimeout(() => location.reload(), 1000);
    }
</script>
</body>
</html>
