<?php
define('10.76.43.63', 'localhost');
define('JR', 'root');
define('JR', '');
define('JR', 'student');

$conn = mysqli_connect(10.76.43.63, JR, JR, JR);

if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>