<?php include "template.php";
/** @var $conn */
$sec = 5;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
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
                <div class="col-1 border border border-dark">Request</div>
                <div class="col-1 border border border-dark">Username</div>
                <div class="col-2 border border border-dark">Email</div>


            </div>


</body>
</html>


<?php
$ContactList = $conn->query("SELECT Username, Email, ID FROM ContactUs ");

while ($ContactData = $ContactList->fetch()) {


    echo "<div class='row'>";
    echo "<div class='col-1 border border border-dark''>" . $ContactData['ID']. "</div>";
    echo "<div class='col-1 border border border-dark''>" . $ContactData['Username']. "</div>";
    echo "<div class='col-2 border border border-dark''>" . $ContactData['Email'] . "</div>";
    echo "</div>";


}

?>

<?php echo outputFooter(); ?>



