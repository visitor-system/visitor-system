<?php 
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$duplicateMessage = '';
$editDepartment = null;

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>
        alert('Department has been deleted.');
        window.location='create_department.php';
        </script>";
    } else {
        echo "<script>
        alert('Failed to delete department.');
        window.location='create_department.php';
        </script>";
    }
    $stmt->close();
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_id'])) {
        // Edit department
        $id = intval($_POST['edit_id']);
        $name = trim($_POST['edit_name']);
        $status = $_POST['edit_status'];

        $stmt = $conn->prepare("UPDATE departments SET name=?, status=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $status, $id);
        if($stmt->execute()){
            echo "<script>
                alert('Department information has been updated.');
                window.location='create_department.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to update department.');
                window.location='create_department.php';
            </script>";
        }
        $stmt->close();
        exit;
    } else {
        // Add department
        $name = trim($_POST['name']);
        $status = $_POST['status'];

        // Check duplicate
        $stmt = $conn->prepare("SELECT id FROM departments WHERE name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $duplicateMessage = "❌ Department <strong>$name</strong> already exists.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO departments (name, status) VALUES (?,?)");
            $stmtInsert->bind_param("ss", $name, $status);
            if ($stmtInsert->execute()) {
                echo "<script>
                alert('Department added successfully.');
                window.location='create_department.php';
                </script>";
            } else {
                $duplicateMessage = "❌ Failed to add department: " . $conn->error;
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}

// Handle Edit GET
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editDepartment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch departments
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC");

// Breadcrumbs
$breadcrumbs = [
    ['title'=>'Dashboard','url'=>'dashboard.php','icon'=>'tachometer-alt'],
    ['title'=>'Department Management','icon'=>'sitemap']
];

echo erp_header('Department Management', $breadcrumbs);
?>

<?php if($duplicateMessage): ?>
    <?php echo erp_alert($duplicateMessage,'danger'); ?>
<?php endif; ?>

<!-- Add/Edit Department Form -->
<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-sitemap"></i> <?= $editDepartment ? 'Edit Department' : 'Add New Department' ?></h3>
    </div>
    <form method="POST" class="p-3">
        <?php if($editDepartment): ?>
            <input type="hidden" name="edit_id" value="<?= $editDepartment['id'] ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Department Name</label>
                <input type="text" name="<?= $editDepartment ? 'edit_name':'name' ?>" class="form-control" value="<?= htmlspecialchars($editDepartment['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="<?= $editDepartment ? 'edit_status':'status' ?>" class="form-control" required>
                    <option value="">Select Status</option>
                    <option value="Active" <?= ($editDepartment['status'] ?? '')=='Active'?'selected':'' ?>>Active</option>
                    <option value="Inactive" <?= ($editDepartment['status'] ?? '')=='Inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="erp-btn erp-btn-primary"><?= $editDepartment ? 'Update Department':'Add Department' ?></button>
            <?php if($editDepartment): ?>
                <a href="create_department.php" class="erp-btn erp-btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Departments Table -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-sitemap"></i> Department List</h3>
    </div>
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows>0): ?>
                    <?php while($row=$result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= erp_badge($row['status'], $row['status']=='Active'?'success':'danger') ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="erp-btn erp-btn-warning erp-btn-sm" onclick="return editAlert();"><i class="fas fa-edit"></i> Edit</a>
                                <a href="javascript:void(0)" class="erp-btn erp-btn-danger erp-btn-sm" onclick="deleteConfirm('?delete=<?= $row['id'] ?>','department')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><br>No departments found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteConfirm(url, type){
    if(confirm(`Are you sure you want to delete this ${type}?`)){
        window.location = url;
    }
}

function editAlert(){
    alert('You can edit this department now.');
    return true; // continue to edit page
}
</script>

<?php echo erp_footer(); ?>
