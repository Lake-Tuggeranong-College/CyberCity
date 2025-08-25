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
$where = $_GET["where"];

$sql = "SELECT $column FROM $table WHERE $where";
$result = $conn->query($sql);

if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo "No results";
}

$conn->close();
?>
