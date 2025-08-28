<?php
$servername = "10.177.200.71";
$username = "CyberCity";
$password = "CyberCity";
$dbname = "CyberCity";

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table = $_GET["table"];
$column = $_GET["column"];
$value = $_GET["value"];
$where = $_GET["where"];

$sql = "UPDATE $table SET $column = '$value' WHERE $where";

if($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}
$conn->close();
?>
