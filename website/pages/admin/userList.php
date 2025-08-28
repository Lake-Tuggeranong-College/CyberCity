<?php
include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    <link rel="stylesheet" href="../../assets/css/moduleList.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">User Accounts</h1>

    <!-- Tabs for Enabled and Disabled Users -->
    <ul class="nav nav-tabs" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="enabled-tab" data-bs-toggle="tab" data-bs-target="#enabled" type="button" role="tab">Enabled Users</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="disabled-tab" data-bs-toggle="tab" data-bs-target="#disabled" type="button" role="tab">Disabled Users</button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="userTabsContent">
        <!-- Enabled Users -->
        <div class="tab-pane fade show active" id="enabled" role="tabpanel">
            <?php
            $enabledUsers = $conn->query("SELECT ID, Username, AccessLevel FROM Users WHERE Enabled=1 ORDER BY ID DESC");
            if ($enabledUsers->rowCount() > 0) {
                echo '<div class="list-group">';
                while ($user = $enabledUsers->fetch()) {
                    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                    echo '<div><strong>' . htmlspecialchars($user["Username"]) . '</strong> (Access Level: ' . $user["AccessLevel"] . ')</div>';
                    echo '<div>';
                    echo '<a href="userEdit.php?UserID=' . $user["ID"] . '" class="btn btn-sm btn-primary me-2">Edit</a>';
                    echo '<a href="userResetPassword.php?UserID=' . $user["ID"] . '" class="btn btn-sm btn-secondary">Password</a>';
                    echo '</div></div>';
                }
                echo '</div>';
            } else {
                echo '<p class="text-muted">No enabled users found.</p>';
            }
            ?>
        </div>

        <!-- Disabled Users -->
        <div class="tab-pane fade" id="disabled" role="tabpanel">
            <?php
            $disabledUsers = $conn->query("SELECT ID, Username, AccessLevel FROM Users WHERE Enabled=0 ORDER BY ID DESC");
            if ($disabledUsers->rowCount() > 0) {
                echo '<div class="list-group">';
                while ($user = $disabledUsers->fetch()) {
                    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                    echo '<div><strong>' . htmlspecialchars($user["Username"]) . '</strong> (Access Level: ' . $user["AccessLevel"] . ')</div>';
                    echo '<div>';
                    echo '<a href="userEdit.php?UserID=' . $user["ID"] . '" class="btn btn-sm btn-warning me-2">Edit</a>';
                    echo '<a href="userResetPassword.php?UserID=' . $user["ID"] . '" class="btn btn-sm btn-secondary">Password</a>';
                    echo '</div></div>';
                }
                echo '</div>';
            } else {
                echo '<p class="text-muted">No disabled users found.</p>';
            }
            ?>
        </div>
    </div>
</div>
