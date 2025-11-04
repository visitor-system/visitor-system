<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$alertMessage = '';
$alertType = '';
$editCompany = null;
$formErrors = [];

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
    $isEdit = isset($_POST['edit_id']);

    $name = trim($_POST[$isEdit ? 'edit_name' : 'name'] ?? '');
    $address = trim($_POST[$isEdit ? 'edit_address' : 'address'] ?? '');
    $status = $_POST[$isEdit ? 'edit_status' : 'status'] ?? '';

    // Validation
    if ($name === '')
        $formErrors['name'] = 'Please enter company name.';
    if ($address === '')
        $formErrors['address'] = 'Please enter address.';
    if ($status === '')
        $formErrors['status'] = 'Please select status.';

    if (empty($formErrors)) {
        if ($isEdit) {
            $id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE companies SET name=?, location=?, status=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $address, $status, $id);
            if ($stmt->execute()) {
                $alertMessage = '✅ Company information has been updated.';
                $alertType = 'success';
            } else {
                $alertMessage = '❌ Failed to update company.';
                $alertType = 'danger';
            }
            $stmt->close();
        } else {
            // Check duplicate
            $stmt = $conn->prepare("SELECT id FROM companies WHERE name=?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $formErrors['name'] = "Company <strong>$name</strong> already exists.";
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO companies (name, location, status) VALUES (?,?,?)");
                $stmtInsert->bind_param("sss", $name, $address, $status);
                if ($stmtInsert->execute()) {
                    $alertMessage = '✅ Company added successfully.';
                    $alertType = 'success';
                } else {
                    $alertMessage = '❌ Failed to create company: ' . $conn->error;
                    $alertType = 'danger';
                }
                $stmtInsert->close();
            }
            $stmt->close();
        }
    } else {
        $alertType = 'danger';
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

<!-- Styles for Gray Form Card -->
<style>
    .erp-card.form-card {
        background-color: #dde0e2ff;
        border: 1px solid #ccc;
        color: #333;
    }

    .erp-card.form-card .form-label {
        color: #333;
    }
</style>

<!-- Alert -->
<?php if ($alertMessage): ?>
    <div class="alert alert-<?= $alertType ?> mt-2"><?= $alertMessage ?></div>
<?php endif; ?>

<!-- Add/Edit Company Form -->
<div class="erp-card form-card mb-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-building"></i>
            <?= $editCompany ? 'Edit Company' : 'Add New Company' ?></h3>
    </div>

    <form method="POST" class="p-3">
        <?php if ($editCompany): ?>
            <input type="hidden" name="edit_id" value="<?= $editCompany['id'] ?>">
        <?php endif; ?>

        <div class="row g-3 align-items-end">
            <!-- Company Name -->
            <div class="col-md-3">
                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="<?= $editCompany ? 'edit_name' : 'name' ?>" class="form-control"
                    value="<?= htmlspecialchars($editCompany['name'] ?? ($_POST['name'] ?? '')) ?>">
                <?php if (!empty($formErrors['name'])): ?>
                    <div class="text-danger small"><?= $formErrors['name'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Address -->
            <div class="col-md-3">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="<?= $editCompany ? 'edit_address' : 'address' ?>" class="form-control"
                    value="<?= htmlspecialchars($editCompany['location'] ?? ($_POST['address'] ?? '')) ?>">
                <?php if (!empty($formErrors['address'])): ?>
                    <div class="text-danger small"><?= $formErrors['address'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="<?= $editCompany ? 'edit_status' : 'status' ?>" class="form-control">
                    <option value="">Select Status</option>
                    <option value="Active" <?= ($editCompany['status'] ?? ($_POST['status'] ?? '')) == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($editCompany['status'] ?? ($_POST['status'] ?? '')) == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if (!empty($formErrors['status'])): ?>
                    <div class="text-danger small"><?= $formErrors['status'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="col-md-3 text-start">
                <button type="submit" class="erp-btn erp-btn-primary w-100">
                    <?= $editCompany ? 'Update Company' : 'Create Company' ?>
                </button>
                <?php if ($editCompany): ?>
                    <a href="create_company.php" class="erp-btn erp-btn-secondary w-100 mt-2">Cancel</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Companies Table -->
<div class="erp-card">
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= erp_badge($row['status'], $row['status'] == 'Active' ? 'success' : 'danger') ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="erp-btn erp-btn-warning erp-btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="erp-btn erp-btn-danger erp-btn-sm"
                                    onclick="deleteConfirm('?delete=<?= $row['id'] ?>','company')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>No companies found
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
</script>

<?php echo erp_footer(); ?>