<?php
    // Base-line redirect URL so the SSH doesn't get flipping confused af
    const BASE_URL = '/CyberCity/website/';

    // Had to be done because PHPStorm wouldn't play ball
    $serverHost = "10.177.200.71";
    $dbUsr = "CyberCity";
    $dbPwd = "CyberCity";
    $dbName = "CyberCity";


    // PDO options for maximum error handling
    $pdoFullOpt = [
        PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,  // Set error mode to exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,        // Set default fetch mode to associative array
        PDO::ATTR_EMULATE_PREPARES      => false,                   // Use native prepared statements
        PDO::ATTR_PERSISTENT            => true,                    // Keeps database connection alive across scripts
    ];

    // Create PDO instance
    try {
        $pdo = new PDO("mysql:host=$serverHost;dbname=$dbName", $dbUsr, $dbPwd, $pdoFullOpt);
        // echo "Connected successfully";

    } catch (PDOException $e) {
        // Failed to connect the database
        die("Connection failed: " . $e -> getMessage());
    }
?>
