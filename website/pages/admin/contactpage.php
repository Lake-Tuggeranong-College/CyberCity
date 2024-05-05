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
                <div class="contactTable" style="min-width: 30px; max-width: 30%"><strong>Request</strong></div>
                <div class="contactTable" style="min-width: 30px; max-width: 30%"><strong>Username</strong></div>
                <div class="contactTable" style="min-width: 300px; max-width: 30%"><strong>Email</strong></div>


            </div>


</body>
</html>


<?php
$ContactList = $conn->query("SELECT Username, Email, ID FROM ContactUs ");

while ($ContactData = $ContactList->fetch()) {


    echo "<div class='row'>";
    echo "<div class='contactTable' style='min-width: 30px; max-width: 30%'>" . $ContactData['ID']. "</div>";
    echo "<div class='contactTable' style='min-width: 30px; max-width: 30%'>" . $ContactData['Username']. "</div>";
    echo "<div class='contactTable' style='min-width: 300px; max-width: 30%'>" . $ContactData['Email'] . "</div>";
    echo "</div>";


}

?>
</div>
</div>
</div>


<?php echo outputFooter(); ?>



