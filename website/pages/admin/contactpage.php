<?php
// Include your database connection
include "../../includes/template.php";

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_read_id'], $_POST['current_status'])) {
    $id = intval($_POST['toggle_read_id']);
    $newStatus = $_POST['current_status'] == 1 ? 0 : 1;

    try {
        $updateStmt = $conn->prepare("UPDATE ContactUs SET IsRead = ? WHERE ID = ?");
        $updateStmt->execute([$newStatus, $id]);
    } catch (PDOException $e) {
        die("Update error: " . $e->getMessage());
    }
}

// Determine view mode
$view = isset($_GET['view']) && $_GET['view'] === 'unread' ? 'unread' : 'all';

// Fetch messages based on view
try {
    if ($view === 'unread') {
        $stmt = $conn->prepare("SELECT ID, Username, Email, IsRead FROM ContactUs WHERE IsRead = 0 ORDER BY ID DESC");
    } else {
        $stmt = $conn->prepare("SELECT ID, Username, Email, IsRead FROM ContactUs ORDER BY ID DESC");
    }
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2 class="text-center mb-4">
    <?= $view === 'unread' ? 'Unread Contact Messages' : 'All Contact Messages' ?>
</h2>

<div class="text-center mb-3">
    <a href="?view=all" class="btn btn-primary btn-sm <?= $view === 'all' ? 'disabled' : '' ?>">View All</a>
    <a href="?view=unread" class="btn btn-secondary btn-sm <?= $view === 'unread' ? 'disabled' : '' ?>">View Unread
        Only</a>
</div>

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
                    <td><?= htmlspecialchars($msg['ID']) ?></td>
                    <td><?= htmlspecialchars($msg['Username']) ?></td>
                    <td><?= htmlspecialchars($msg['Email']) ?></td>
                    <!--                        <td>--><?php //= $msg['IsRead'] ? 'Read' : 'Unread' ?><!--</td>-->
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="toggle_read_id" value="<?= $msg['ID'] ?>">
                            <input type="hidden" name="current_status" value="<?= $msg['IsRead'] ?>">
                            <button type="submit"
                                    class="btn btn-sm <?= $msg['IsRead'] ? 'btn-warning' : 'btn-success' ?>">
                                <?= $msg['IsRead'] ? 'Mark As Unread' : 'Mark As Read' ?>
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center" role="alert">
        <?= $view === 'unread' ? 'No unread messages.' : 'No messages found.' ?>
    </div>
<?php endif; ?>
