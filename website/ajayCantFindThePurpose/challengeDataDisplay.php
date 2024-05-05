<?php
include "template.php";
/** @var $conn */

if (!authorisedAccess(false, true, true)) {
    header("Location:index.php");
}

if (isset($_GET["moduleID"])) {
    $challengeToLoad = $_GET["moduleID"];
} else {
    header("location:challengesList.php");
}

$sql = $conn->query("SELECT  * FROM ModuleData WHERE moduleID = " . $challengeToLoad . " ORDER BY ID DESC");
$result = $sql->fetch();
$reference = $result["Data"];



$sql = $conn->query("SELECT * FROM ChallengeData WHERE moduleID = " . $challengeToLoad . " AND reference =".$reference." ORDER BY ID DESC");
$result = $sql->fetch();
$moduleID = $result["moduleID"];
$reference = $result["reference"];
$challengeData = $result["data"];
?>

<title>Challenge Data</title>

</head>
<body>
<!-- Indicate heading section of the whole page. -->
<header class="container-fluid d-flex align-items-center justify-content-center">
    <h1 class="text-uppercase">Challenge - <?= $moduleID ?></h1>
</header>

<!-- Indicate section (middle part) section of the whole page. -->
<section class="pt-4 pd-2">
    <!-- Boostrap Grid Table System. -->
    <div class="container-fluid text-center user-select-none">
        <div class="row border border-dark-subtle border-2">
            <div class="col fw-bold border-start border-end border-dark-subtle border-2">
                Challenge Data
            </div>
        </div>

        <div class="row border border-top-0 border-dark-subtle border-2">
            <div class="col border-start border-end border-dark-subtle border-2">
                <?= $challengeData ?>
            </div>
        </div>

        <div class="row border border-top-0 border-dark-subtle border-2">
            <p class="text-success fw-bold pt-3">Good luck and have fun!</p>
        </div>

        <!-- Inline CSS styling for Horizontal line. -->
        <hr style="
                    border: none; 
                    position: relative; 
                    margin: 1.5rem 0; 
                    height: 4px; /* Adjust horizontal line thickness.*/
                    color: red; /* Compatible for users using older version of any Web Browser Apps.*/
                    background-color: red;
                ">

</section>

</body>
</html>