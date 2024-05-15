<?php include "../../includes/template.php";
/** @var $conn */
$sec = 60;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'">
    <title>Cyber City - ContactPage</title>
</head>
<body>
<h1>Contact Page</h1>
<div class="container-fluid">
    <div class="container-fluid">
        <div class="row">
            <div class="contactTable" style="min-width: 30px; max-width: 24%"><strong>Request</strong></div>
            <div class="contactTable" style="min-width: 30px; max-width: 24%"><strong>Username</strong></div>
            <div class="contactTable" style="min-width: 300px; max-width: 24%"><strong>Email</strong></div>
            <div class="contactTable" style="min-width: 300px; max-width: 24%"></div>


        </div>


</body>
</html>


<?php
$ContactList = $conn->query("SELECT Username, Email, ID, IsRead FROM ContactUs WHERE IsRead=0 ");

while ($ContactData = $ContactList->fetch()) {


    echo "<div class='row'>";
    echo "<div class='contactTable' style='min-width: 30px; max-width: 24%'>" . $ContactData['ID'] . "</div>";
    echo "<div class='contactTable' style='min-width: 30px; max-width: 24%'>" . $ContactData['Username'] . "</div>";
    echo "<div class='contactTable' style='min-width: 300px; max-width: 24%'>" . $ContactData['Email'] . "</div>";
    echo "<div class='contactTable' style='min-width: 300px; max-width: 24%'>";
    echo "<form method='post' action=' ". BASE_URL."/pages/admin/contactpage.php?ContactID=" . $ContactData['IsRead'] . ">";
    echo "<button type='submit' class='btn btn-outline-danger'>READ</button>";
    echo "</form>";



    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo $ContactData["IsRead"];
        if (isset($_GET["ContactID"])) {
            $postID = $_GET["ContactID"];
            $sql = "UPDATE ContactUs SET IsRead = 1 WHERE ID ='$postID'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $_SESSION["flash_message"] = "message read";
            header("Location:" . BASE_URL . "/pages/admin/contactpage.php");
            echo $ContactData["IsRead"];
        }
    }
    ?>
    </div>
    <?php
    echo "</div>";


}

?>
</div>
</div>
</div>


<?php echo outputFooter(); ?>



