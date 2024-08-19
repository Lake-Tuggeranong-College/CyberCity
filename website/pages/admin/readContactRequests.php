<?php
include "../../includes/template.php";

$sec = 60;
$page = $_SERVER['PHP_SELF'];

if (!authorisedAccess(false, false, true)) {
    header("Location: ../../index.php");
    exit();
}

// Fetch contact list from database
$ContactList = $conn->query("SELECT Username, Email, ID FROM ContactUs WHERE IsRead=1");

// Check if query was successful
if (!$ContactList) {
    die("Error fetching contact list: " . $conn->error);
}
?>

<head>
    <meta http-equiv="refresh" content="<?= $sec ?>;URL='<?= htmlspecialchars($page) ?>'">
    <title>Cyber City - Contact Page</title>
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
        <?php while ($ContactData = $ContactList->fetch_assoc()): ?>
            <div class='row contact-row'>
                <div class='contactTable' style='min-width: 30px; max-width: 30%'><?= htmlspecialchars($ContactData['ID']) ?></div>
                <div class='contactTable' style='min-width: 30px; max-width: 30%'><?= htmlspecialchars($ContactData['Username']) ?></div>
                <div class='contactTable' style='min-width: 300px; max-width: 30%'><?= htmlspecialchars($ContactData['Email']) ?></div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

<?php

// Close tge database connection to clear cache (good PHP practice)
$conn->close();

?>
