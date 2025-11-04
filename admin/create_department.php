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
$errors = [
    'name' => '',
    'status' => ''
];

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
    $name = trim($_POST['name'] ?? $_POST['edit_name'] ?? '');
    $status = $_POST['status'] ?? $_POST['edit_status'] ?? '';

    // Validation
    if (empty($name))
        $errors['name'] = "❌ Please fill this Department";
    if (empty($status))
        $errors['status'] = "❌ Please fill this Status";

    if (!$errors['name'] && !$errors['status']) {
        if (isset($_POST['edit_id'])) {
            // Edit department
            $id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE departments SET name=?, status=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $status, $id);
            if ($stmt->execute()) {
                echo "<script>
                    alert('Department information has been updated.');
                    window.location='create_department.php';
                </script>";
            } else {
                $duplicateMessage = "❌ Failed to update department: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Add department
            $stmt = $conn->prepare("SELECT id FROM departments WHERE name=?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $duplicateMessage = "❌ Department <strong>$name</strong> already exists.";
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO departments (name, status) VALUES (?, ?)");
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
}

// Handle Edit
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
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Department Management', 'icon' => 'sitemap']
];

echo erp_header('Department Management', $breadcrumbs);
?>

<!-- Styles for Gray Form Card -->
<style>
    .erp-card.form-card {
        background-color: #dde0e2ff;
        /* Gray background */
        border: 1px solid #ccc;
        color: #333;
    }

    .erp-card.form-card .form-label {
        color: #333;
    }
</style>

<?php if ($duplicateMessage): ?>
    <?php echo erp_alert($duplicateMessage, 'danger'); ?>
<?php endif; ?>

<!-- Add/Edit Department Form -->
<div class="erp-card form-card mb-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-sitemap"></i>
            <?= $editDepartment ? 'Edit Department' : 'Add New Department' ?>
        </h3>
    </div>

    <form method="POST" class="p-3" id="departmentForm">
        <?php if ($editDepartment): ?>
            <input type="hidden" name="edit_id" value="<?= $editDepartment['id'] ?>">
        <?php endif; ?>

        <div class="row g-3 align-items-end">
            <!-- Department Name -->
            <div class="col-md-3">
                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                <input type="text" name="<?= $editDepartment ? 'edit_name' : 'name' ?>" id="deptName"
                    class="form-control"
                    value="<?= htmlspecialchars($editDepartment['name'] ?? $_POST['name'] ?? '') ?>">
                <?php if ($errors['name']): ?>
                    <div class="text-danger mt-1"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="<?= $editDepartment ? 'edit_status' : 'status' ?>" id="deptStatus" class="form-control">
                    <option value="">Select Status</option>
                    <option value="Active" <?= ($editDepartment['status'] ?? $_POST['status'] ?? '') == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($editDepartment['status'] ?? $_POST['status'] ?? '') == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if ($errors['status']): ?>
                    <div class="text-danger mt-1"><?= $errors['status'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Submit Button Beside Fields -->
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="erp-btn erp-btn-primary w-100">
                    <?= $editDepartment ? 'Update Department' : 'Add Department' ?>
                </button>
            </div>

            <!-- Cancel Button (only in edit mode) -->
            <?php if ($editDepartment): ?>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="create_department.php" class="erp-btn erp-btn-secondary w-100">Cancel</a>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Department List Table -->
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
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= erp_badge($row['status'], $row['status'] == 'Active' ? 'success' : 'danger') ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="erp-btn erp-btn-warning erp-btn-sm"
                                    onclick="return editAlert();"><i class="fas fa-edit"></i> Edit</a>
                                <a href="javascript:void(0)" class="erp-btn erp-btn-danger erp-btn-sm"
                                    onclick="deleteConfirm('?delete=<?= $row['id'] ?>','department')"><i
                                        class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            No departments found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function deleteConfirm(url, type) {
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
            window.location = url;
        }
    }

    function editAlert() {
        alert('You can edit this department now.');
        return true;
    }
</script>

<?php echo erp_footer(); ?>