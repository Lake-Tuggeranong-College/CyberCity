<?php
session_start();
$servername = "10.200.177.71";
$username = "JEDI2023";
$password = "JEDI2023";
$dbname = "JEDI2023";
$errorCaught = false;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $errorCaught = true;
    
    $_SESSION['flash_message'] = "<div class='bg-danger'>The Database cannot be found: " . $servername . ". ".$e."</div>";
}
if (!$errorCaught) {
    //echo "Database connection configured correctly, and database connection good.";
}
//$conn = null;
?>

