<?php
include_once "../../includes/template.php";

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
}
?>

<title>Reset Button</title>

<h1 class='text-primary'> WARNING: pressing this button will make all regular users access level 0! (disabled)</h1>

<form action="resetGame.php" method="post" enctype="multipart/form-data">
    <input type="submit" name="formSubmit" value="Update">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = "UPDATE Users SET Enabled = 0 WHERE AccessLevel ='1'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    header('Location: '. $_SERVER['REQUEST_URI']);

}
?>
