<?php
session_start();

$servername = "10.76.43.63";
$username = "JR";
$password = "JR";
$dbname = "JR";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $errorCaught = true;
    echo "Error: " . $e->getMessage();
}
if (!$errorCaught) {

}
//$conn = null;
?>
