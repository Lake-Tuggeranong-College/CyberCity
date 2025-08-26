<?php
include "../../includes/template.php";
/** @var $conn */

if (!authorisedAccess(false, false, true)) {
    header("Location:../../index.php");
    exit;
}

?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $category_name = trim($_POST['category_name']);
    $project_id = $_POST['projectID'];

    if (!empty($category_name) && !empty($project_id)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Category (CategoryName, ProjectID) VALUES (:name, :project_id)");
            $stmt->execute([
                    ':name' => $category_name,
                    ':project_id' => $project_id
            ]);
            echo "<div class='alert alert-success'>Category '$category_name' added successfully.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}
?>


<meta charset="UTF-8">
<title>Create Category</title>
<h2 class="mb-4">Create New Category</h2>


<!-- Bootstrap 5.3.3 Enhanced Form -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Create New Category</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectID" class="form-label">Select Project</label>
                            <select class="form-select" id="projectID" name="projectID" required>
                                <?php
                                $projectList = $conn->query("SELECT project_id, project_name FROM CyberCity.Projects");
                                while ($row = $projectList->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row['project_id']) . '">' . htmlspecialchars($row['project_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="create_category" class="btn btn-primary">Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>
