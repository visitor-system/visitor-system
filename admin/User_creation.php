<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check admin login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$alertMessage = '';
$alertType = '';
$editUser = null;
$formErrors = [];

// Fetch departments
$departmentsResult = $conn->query("SELECT id, name FROM departments WHERE status='Active' ORDER BY name ASC");
$departments = [];
if ($departmentsResult && $departmentsResult->num_rows > 0) {
    while ($row = $departmentsResult->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $alertMessage = 'User deleted successfully.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Failed to delete user.';
            $alertType = 'danger';
        }
        $stmt->close();
    }
}

// Handle edit GET
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isEdit = isset($_POST['edit_id']);

    $username = trim($isEdit ? ($_POST['edit_username'] ?? '') : ($_POST['username'] ?? ''));
    $email = trim($isEdit ? ($_POST['edit_email'] ?? '') : ($_POST['email'] ?? ''));
    $contact = trim($isEdit ? ($_POST['edit_contact'] ?? '') : ($_POST['contact'] ?? ''));
    $role = $isEdit ? ($_POST['edit_role'] ?? '') : ($_POST['role'] ?? '');
    $status = $isEdit ? ($_POST['edit_status'] ?? '') : ($_POST['status'] ?? '');
    $department = trim($isEdit ? ($_POST['edit_department'] ?? '') : ($_POST['department'] ?? ''));
    $password = $isEdit ? ($_POST['edit_password'] ?? '') : ($_POST['password'] ?? '');

    // Validation
    if ($username === '') $formErrors['username'] = 'Please fill this Full Name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[a-zA-Z]/', $email)) {
        $formErrors['email'] = 'Email must start with a letter.';
    }
    if (!preg_match('/^[0-9]{10}$/', $contact)) $formErrors['contact'] = 'Please enter a valid 10-digit number.';
    if ($role === '') $formErrors['role'] = 'Please select a role.';
    if ($status === '') $formErrors['status'] = 'Please select a status.';
    if ($department === '') $formErrors['department'] = 'Please select a department.';
    if (!$isEdit && $password === '') $formErrors['password'] = 'Password is required.';

    if (empty($formErrors)) {
        if ($isEdit) {
            $id = intval($_POST['edit_id']);
            if ($password !== '') {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, contact=?, role=?, status=?, department=?, password=? WHERE id=?");
                $stmt->bind_param("sssssssi", $username, $email, $contact, $role, $status, $department, $hashed, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, contact=?, role=?, status=?, department=? WHERE id=?");
                $stmt->bind_param("ssssssi", $username, $email, $contact, $role, $status, $department, $id);
            }
            if ($stmt->execute()) {
                $alertMessage = 'User updated successfully.';
                $alertType = 'success';
            } else {
                $alertMessage = 'Failed to update user.';
                $alertType = 'danger';
            }
            $stmt->close();
        } else {
            $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email=?");
            $stmtCheck->bind_param("s", $email);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            if ($stmtCheck->num_rows > 0) {
                $formErrors['email'] = 'A user with this email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username,email,contact,role,status,department,password) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("sssssss", $username, $email, $contact, $role, $status, $department, $hashed);
                if ($stmt->execute()) {
                    $alertMessage = 'User added successfully.';
                    $alertType = 'success';
                } else {
                    $alertMessage = 'Failed to add user.';
                    $alertType = 'danger';
                }
                $stmt->close();
            }
            $stmtCheck->close();
        }
    } else {
        $alertType = 'danger';
    }
}

// Fetch users
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'User Management', 'icon' => 'users']
];

echo erp_header('User Management', $breadcrumbs);
?>

<!-- Add/Edit User Form -->
<div class="erp-card mb-3" style="background-color: #dde0e2ff; border-radius: 8px;"> <!-- Form background color -->
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-user-plus"></i>
            <?= $editUser ? 'Edit User' : 'Add New User' ?>
        </h3>
    </div>

    <form method="POST" class="p-3">
        <?php if ($editUser): ?>
            <input type="hidden" name="edit_id" value="<?= $editUser['id'] ?>">
        <?php endif; ?>

        <div class="row g-3 align-items-end">
            <!-- Full Name -->
            <div class="col-md-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="<?= $editUser ? 'edit_username' : 'username' ?>" class="form-control"
                       value="<?= htmlspecialchars($editUser['username'] ?? ($_POST['username'] ?? '')) ?>">
                <?php if (!empty($formErrors['username'])): ?>
                    <div class="text-danger small"><?= $formErrors['username'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="col-md-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="<?= $editUser ? 'edit_email' : 'email' ?>" class="form-control"
                       value="<?= htmlspecialchars($editUser['email'] ?? ($_POST['email'] ?? '')) ?>">
                <?php if (!empty($formErrors['email'])): ?>
                    <div class="text-danger small"><?= $formErrors['email'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Contact -->
            <div class="col-md-3">
                <label class="form-label">Contact <span class="text-danger">*</span></label>
                <input type="text" maxlength="10" name="<?= $editUser ? 'edit_contact' : 'contact' ?>" class="form-control"
                       value="<?= htmlspecialchars($editUser['contact'] ?? ($_POST['contact'] ?? '')) ?>">
                <?php if (!empty($formErrors['contact'])): ?>
                    <div class="text-danger small"><?= $formErrors['contact'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="col-md-3">
                <label class="form-label"><?= $editUser ? 'New Password (optional)' : 'Password' ?> <?= !$editUser ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="password" name="<?= $editUser ? 'edit_password' : 'password' ?>" class="form-control">
                <?php if (!empty($formErrors['password'])): ?>
                    <div class="text-danger small"><?= $formErrors['password'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Role -->
            <div class="col-md-3">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <?php $currentRole = strtolower(trim($editUser['role'] ?? ($_POST['role'] ?? ''))); ?>
                <select name="<?= $editUser ? 'edit_role' : 'role' ?>" class="form-control">
                    <option value="">Select Role</option>
                    <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="security" <?= $currentRole === 'security' ? 'selected' : '' ?>>Security</option>
                    <option value="host" <?= $currentRole === 'host' ? 'selected' : '' ?>>Host</option>
                </select>
                <?php if (!empty($formErrors['role'])): ?>
                    <div class="text-danger small"><?= $formErrors['role'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <?php $currentStatus = strtolower(trim($editUser['status'] ?? ($_POST['status'] ?? ''))); ?>
                <select name="<?= $editUser ? 'edit_status' : 'status' ?>" class="form-control">
                    <option value="">Select Status</option>
                    <option value="Active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if (!empty($formErrors['status'])): ?>
                    <div class="text-danger small"><?= $formErrors['status'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Department -->
            <div class="col-md-3">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select name="<?= $editUser ? 'edit_department' : 'department' ?>" class="form-control">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dep): 
                        $selected = ($editUser['department'] ?? ($_POST['department'] ?? '')) == $dep['name'] ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($dep['name']) ?>" <?= $selected ?>><?= htmlspecialchars($dep['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($formErrors['department'])): ?>
                    <div class="text-danger small"><?= $formErrors['department'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Submit Button beside Department -->
            <div class="col-md-3 text-start">
                <button type="submit" class="erp-btn erp-btn-primary w-100">
                    <?= $editUser ? 'Update User' : 'Add User' ?>
                </button>
                <?php if ($editUser): ?>
                    <a href="User_creation.php" class="erp-btn erp-btn-secondary w-100 mt-2">Cancel</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($alertMessage): ?>
            <div class="alert alert-<?= $alertType ?> mt-3"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>
    </form>
</div>

<!-- User List -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-users"></i> User List</h3>
        <span class="badge bg-primary"><?= $result->num_rows ?> Users</span>
    </div>
    <div class="erp-table-container">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= erp_badge(ucfirst($row['role']), $row['role'] == 'admin' ? 'primary' : ($row['role'] == 'security' ? 'warning' : 'info')) ?></td>
                            <td><?= erp_badge($row['status'], strtolower($row['status']) == 'active' ? 'success' : 'danger') ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="erp-btn erp-btn-warning erp-btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" class="erp-btn erp-btn-danger erp-btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo erp_footer(); ?>
