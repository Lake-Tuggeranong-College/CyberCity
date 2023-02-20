<?php
session_start();

$servername = "10.76.43.63";
$username = "JH";
$password = "Tarajade89";
$dbname = "JH";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>