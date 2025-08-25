<?php
// Include your database connection
include "../../includes/template.php";

// Handle "Read" button submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    $id = intval($_POST['mark_read_id']);
    try {
        $updateStmt = $conn->prepare("UPDATE ContactUs SET IsRead = 1 WHERE ID = ?");
        $updateStmt->execute([$id]);
    } catch (PDOException $e) {
        die("Update error: " . $e->getMessage());
    }
}

// Fetch unread messages
try {
    $stmt = $conn->prepare("SELECT ID, Username, Email FROM ContactUs WHERE IsRead = 0");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2 class="text-center mb-4">Unread Contact Messages</h2>

<?php if (count($messages) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                       td><?= htmlspecialchars($msg['ID']) ?></td>
                        <td><?= htmlspecialchars($msg['Username']) ?></td>
                        <td><?= htmlspecialchars($msg['Email']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="mark_read_id" value="<?= $msg['ID'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Read</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center" role="alert">
        No unread messages.
    </div>
<?php endif; ?>
