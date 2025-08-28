<?php
include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
    exit;
}

if (isset($_GET["UserID"])) {
    $userToLoad = $_GET["UserID"];
    $sql = $conn->query("SELECT * FROM Users WHERE ID = " . intval($userToLoad));
    $userInformation = $sql->fetch();

    if ($userInformation) {
        $userID = $userInformation["ID"];
        $userName = $userInformation["Username"];
        $userAccessLevel = $userInformation["AccessLevel"];
        $userEnabled = $userInformation["Enabled"];
        $userScore = $userInformation["Score"];
    } else {
        header("Location:userList.php");
        exit;
    }
} else {
    header("Location:userList.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@ss/bootstrap.min.css
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet           background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit User Information</h4>
                </div>
                <div class="card-body">
                    <form action="userEdit.php?UserID=<?= $userToLoad ?>" method="post">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Username</label>
                            <input type="text" name="userName" id="userName" class="form-control" required
                                   value="<?= htmlspecialchars($userName ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="AccessLevel" class="form-label">Access Level</label>
                            <select name="AccessLevel" id="AccessLevel" class="form-select">
                                <option value="1" <?= $userAccessLevel == 1 ? 'selected' : '' ?>>User</option>
                                <option value="2" <?= $userAccessLevel == 2 ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Enabled" class="form-label">Status</label>
                            <select name="Enabled" id="Enabled" class="form-select">
                                <option value="1" <?= $userEnabled == 1 ? 'selected' : '' ?>>Enabled</option>
                                <option value="0" <?= $userEnabled == 0 ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Score" class="form-label">Current Score</label>
                            <input type="number" name="Score" id="Score" class="form-control" required
                                   value="<?= htmlspecialchars($userScore ?? '') ?>">
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="formSubmit" class="btn btn-success btn-lg">
                                <i class="bi bi-save me-2"></i>Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>