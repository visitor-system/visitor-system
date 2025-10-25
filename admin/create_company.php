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
$editCompany = null;

// Handle delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM companies WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>
        alert('Company has been deleted.');
        window.location='create_company.php';
        </script>";
    } else {
        echo "<script>
        alert('Failed to delete company.');
        window.location='create_company.php';
        </script>";
    }
    $stmt->close();
  exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['edit_id'])) {
        // Update company
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['edit_name']);
    $address = trim($_POST['edit_address']);
    $status = $_POST['edit_status'];

    $stmt = $conn->prepare("UPDATE companies SET name=?, location=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $address, $status, $id);
    if($stmt->execute()){
        echo "<script>
            alert('Company information has been updated.');
            window.location='create_company.php';
        </script>";
    } else {
        echo "<script>
            alert('Failed to update company.');
            window.location='create_company.php';
        </script>";
    }
    $stmt->close();
    exit;
  } else {
        // Add new company
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];

        // Check for duplicate
        $stmt = $conn->prepare("SELECT id FROM companies WHERE name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $duplicateMessage = "❌ Company <strong>$name</strong> already exists.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO companies (name, location, status) VALUES (?,?,?)");
            $stmtInsert->bind_param("sss", $name, $address, $status);
            if ($stmtInsert->execute()) {
                echo "<script>
                alert('Company added successfully.');
                window.location='create_company.php';
                </script>";
            } else {
                $duplicateMessage = "❌ Failed to create company: " . $conn->error;
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}

// Handle edit GET
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editCompany = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all companies
$result = $conn->query("SELECT * FROM companies ORDER BY id DESC");

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Company Management', 'icon' => 'building']
];

echo erp_header('Company Management', $breadcrumbs);
?>

<?php if ($duplicateMessage): ?>
    <?php echo erp_alert($duplicateMessage, 'danger'); ?>
<?php endif; ?>

<!-- Add/Edit Company Form -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-building"></i> <?= $editCompany ? 'Edit Company' : 'Add New Company' ?></h3>
    </div>

    <form method="POST">
        <?php if ($editCompany): ?>
            <input type="hidden" name="edit_id" value="<?= $editCompany['id'] ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">Company Name</label>
                    <input type="text" name="<?= $editCompany ? 'edit_name' : 'name' ?>" class="erp-form-control" value="<?= htmlspecialchars($editCompany['name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">Address</label>
                    <input type="text" name="<?= $editCompany ? 'edit_address' : 'address' ?>" class="erp-form-control" value="<?= htmlspecialchars($editCompany['location'] ?? '') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">Status</label>
                    <select name="<?= $editCompany ? 'edit_status' : 'status' ?>" class="erp-form-control" required>
                        <option value="">Select Status</option>
                        <option value="Active" <?= ($editCompany['status'] ?? '')=='Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($editCompany['status'] ?? '')=='Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="erp-btn erp-btn-primary"><?= $editCompany ? 'Update Company' : 'Create Company' ?></button>
            <?php if ($editCompany): ?>
                <a href="create_company.php" class="erp-btn erp-btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Companies Table -->
<div class="erp-card mt-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-building"></i> Company List</h3>
    </div>
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= erp_badge($row['status'], $row['status']=='Active' ? 'success':'danger') ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="erp-btn erp-btn-warning erp-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <button class="erp-btn erp-btn-danger erp-btn-sm" onclick="deleteConfirm('?delete=<?= $row['id'] ?>','company')"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><br>No companies found</td>
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
</script>

<?php echo erp_footer(); ?>
