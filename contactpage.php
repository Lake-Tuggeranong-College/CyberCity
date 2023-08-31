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
<h1 class='text-primary'>ContactPage</h1>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
        </thead>
</body>
</html>


<?php
$ContactList = $conn->query("SELECT Username, Email FROM ContactUs ");

while ($ContactData = $ContactList->fetch()) {

        echo "<tr>";
        echo "<td>" . $ContactData['Username'] . "</td>";
        echo "<td>" . $ContactData['Email'] . "</td>";
        echo "</tr>";


}

?>



