<?php
include "../../includes/template.php";

$sec = 60;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(false, false, true)) {
    header("Location: ../../index.php");
    exit();
}

// Set to true as default for requested contacts from user
$isRead = 1;

// Fetch contact list from database
$query = "SELECT Username, Email, ID 
          FROM ContactUs 
          WHERE IsRead = :IsRead";

$stmt = $conn->prepare($query);
$stmt->bindParam(':IsRead', $isRead, PDO::PARAM_INT);
$stmt->execute();

// Check if query was successful
if (!$stmt) {
    die("Error fetching contact list: " . $conn->errorInfo());
}
?>

<head>
    <meta http-equiv="refresh" content="<?= $sec ?>;URL='<?= htmlspecialchars($page) ?>'">
    <title>Cyber City - Contact Page</title> <!-- Probably not needed but well, I'll just keep it here in case -->
</head>

<body>
    <h1>Contact Requests</h1>

    <div class="container-fluid">
        <div class="row contact-header">
            <div class="contactTable" style="min-width: 30px; max-width: 30%"><strong>Request ID</strong></div>
            <div class="contactTable" style="min-width: 30px; max-width: 30%"><strong>Username</strong></div>
            <div class="contactTable" style="min-width: 300px; max-width: 30%"><strong>Email</strong></div>
        </div>

        <!-- Get stored requests from users in database data -->
        <?php while ($contactData = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class='row contact-row'>
                <div class='contactTable' style='min-width: 30px; max-width: 30%'><?= htmlspecialchars($contactData['ID']) ?></div>
                <div class='contactTable' style='min-width: 30px; max-width: 30%'><?= htmlspecialchars($contactData['Username']) ?></div>
                <div class='contactTable' style='min-width: 300px; max-width: 30%'><?= htmlspecialchars($contactData['Email']) ?></div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

<?php

// Close the database connection to clear cache (good PHP practice)
$conn = null;

?>
