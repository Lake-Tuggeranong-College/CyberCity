<?php
include_once "../../includes/template.php";

/* -------------------------------------------------
   Safe redirect (works even after output started)
-------------------------------------------------- */
function safe_redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    }
    // JS fallback if headers already sent by template.php
    echo '<script>location.replace(' . json_encode($url, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) . ');</script>';
    exit;
}

/* -------------------------------------------------
   Auth: allow any logged-in user (adjust if needed)
   NOTE: Your previous code redirected when access
   WAS granted (backwards) and also forced admin-only.
-------------------------------------------------- */
if (!authorisedAccess(false, true, true)) { // <-- allow students/teachers (example)
    safe_redirect("../../index.php");
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    safe_redirect("../../index.php");
}

/* -------------------------------------------------
   Fetch current user
-------------------------------------------------- */
$query = "SELECT Username, user_email, profile_picture FROM Users WHERE ID = :userId";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

/* -------------------------------------------------
   Avatar paths/URLs
-------------------------------------------------- */
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$avatarsDirFs  = __DIR__ . "/user/userAvatars";
if (!is_dir($avatarsDirFs)) {
    $avatarsDirFs = __DIR__ . "/userAvatars";
    if (!is_dir($avatarsDirFs)) {
        @mkdir($avatarsDirFs, 0775, true);
    }
}
if (!is_dir($avatarsDirFs)) {
    @mkdir($avatarsDirFs, 0775, true);
}

$avatarsDirUrl = rtrim(BASE_URL, "/") . "/pages/user/userAvatars";
$avatarFs      = rtrim($avatarsDirFs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "UUID{$userId}.webp";
$avatarUrl     = is_file($avatarFs)
    ? $avatarsDirUrl . "/UUID{$userId}.webp?v=" . filemtime($avatarFs)
    : rtrim(BASE_URL, "/") . "/pages/user/userAvatars/default.jpg";

/* -------------------------------------------------
   POST: save username/email and optional avatar
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitise the input data
    $username = sanitise_data($_POST['username'] ?? '');
    $email    = sanitise_data($_POST['email'] ?? '');

    // Update the user's data in the database
    $q = $conn->prepare("UPDATE Users SET Username = :newUsername, user_email = :newEmail WHERE ID = :userId");
    $q->bindParam(':newUsername', $username);
    $q->bindParam(':newEmail', $email);
    $q->bindParam(':userId', $userId, PDO::PARAM_INT);
    $q->execute();

    // Optional avatar upload
    if (!empty($_FILES['avatar']) && is_array($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $err = $_FILES['avatar']['error'];

        if ($err === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['avatar']['tmp_name'];
            $size = (int)($_FILES['avatar']['size'] ?? 0);

            // Cap 4 MB
            if ($size > 4 * 1024 * 1024) {
                $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Avatar too large. Please upload an image under 4 MB.</div>";
                safe_redirect($_SERVER['REQUEST_URI']);
            }

            $info = @getimagesize($tmp);
            if ($info === false) {
                $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Invalid image. Please upload JPG, PNG, WEBP or GIF.</div>";
                safe_redirect($_SERVER['REQUEST_URI']);
            }

            $mime = $info['mime'] ?? '';
            $src  = null;
            switch ($mime) {
                case 'image/jpeg': $src = @imagecreatefromjpeg($tmp); break;
                case 'image/png':  $src = @imagecreatefrompng($tmp);  break;
                case 'image/webp': $src = @imagecreatefromwebp($tmp); break;
                case 'image/gif':  $src = @imagecreatefromgif($tmp);  break;
            }
            if (!$src) {
                $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Unsupported format. Use JPG, PNG, WEBP or GIF.</div>";
                safe_redirect($_SERVER['REQUEST_URI']);
            }

            if (!is_dir($avatarsDirFs)) {
                @mkdir($avatarsDirFs, 0775, true);
            }
            if (!is_writable($avatarsDirFs)) {
                imagedestroy($src);
                $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Avatar folder is not writable: <code>" . h($avatarsDirFs) . "</code></div>";
                safe_redirect($_SERVER['REQUEST_URI']);
            }

            // Convert to WebP and overwrite UUID<id>.webp
            $w = imagesx($src); $h = imagesy($src);
            $dst = imagecreatetruecolor($w, $h);
            imagealphablending($dst, false); imagesavealpha($dst, true);
            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);

            $saved = @imagewebp($dst, $avatarFs, 82);
            imagedestroy($src);
            imagedestroy($dst);

            if (!$saved) {
                $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Failed to save avatar. Ensure PHP GD has WebP enabled.</div>";
                safe_redirect($_SERVER['REQUEST_URI']);
            }
            @chmod($avatarFs, 0644);

            // Update DB filename to UUID<id>.webp (single stable file name)
            $newFilename = "UUID{$userId}.webp";
            $uq = $conn->prepare("UPDATE Users SET profile_picture = :f WHERE ID = :id");
            $uq->execute([':f' => $newFilename, ':id' => $userId]);

            // Refresh the displayed URL for this request (cache-bust)
            $avatarUrl = $avatarsDirUrl . "/{$newFilename}?v=" . filemtime($avatarFs);
            $userData['profile_picture'] = $newFilename;
        } else {
            $_SESSION["flash_message"] = "<div class='bg-danger text-white p-2 text-center'>Avatar upload failed (error {$err}).</div>";
            safe_redirect($_SERVER['REQUEST_URI']);
        }
    }

    // Update session data with new changes
    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;

    // Flash message
    $_SESSION["flash_message"] = "<div class='bg-success text-white p-2 text-center'>Account Updated Successfully!</div>";

    // Redirect (avoid 'headers already sent' by using safe_redirect)
    safe_redirect("../../index.php");
}
?>
<div class="container mt-5 mb-5">
    <div class="row g-4">
        <!-- Profile Section -->
        <div class="col-md-4 text-center">
            <div class="card bg-dark text-white p-4">
                <img src="<?= h($avatarUrl) ?>"
                     class="rounded-circle mx-auto"
                     width="150" height="150"
                     alt="Profile Picture"
                     style="object-fit:cover;object-position:center;">
                <h5 class="mt-3"><?= h($userData['Username']); ?></h5>
                <p><?= h($userData['user_email']); ?></p>
            </div>
        </div>

        <!-- Edit Form Section -->
        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="mb-4 text-center">Edit Account</h4>
                <form action="editAccount.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= h($userData['Username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= h($userData['user_email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar (JPG, PNG, WEBP, GIF — max 4 MB)</label>
                        <input type="file" id="avatar" name="avatar" class="form-control"
                               accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
