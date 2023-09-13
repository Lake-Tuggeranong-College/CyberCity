<?php include "template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:index.php");
}

?>


<title>reset button</title>
<h1 class='text-primary'> warning pressing this button will make all regular users access level 0 (disabled)</h1>
    <form action="resetGame.php" method="post" enctype="multipart/form-data">

            <input type="submit" name="formSubmit" value="Update">
    </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "UPDATE Users SET Enabled = 0 WHERE AccessLevel ='1'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}


echo outputFooter();
?>