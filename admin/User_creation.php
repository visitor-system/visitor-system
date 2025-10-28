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

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $alertMessage = 'User has been deleted successfully.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Failed to delete user.';
            $alertType = 'error';
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
    if (isset($_POST['edit_id'])) {
        // Update user
        $id = intval($_POST['edit_id']);
        $username = trim($_POST['edit_username']);
        $email = trim($_POST['edit_email']);
        $contact = trim($_POST['edit_contact']);
        $role = $_POST['edit_role'];
        $status = $_POST['edit_status'];
        $department = trim($_POST['edit_department']);
        $password = $_POST['edit_password'];

        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, contact=?, role=?, status=?, department=?, password=? WHERE id=?");
            $stmt->bind_param("sssssssi", $username, $email, $contact, $role, $status, $department, $hashedPassword, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, contact=?, role=?, status=?, department=? WHERE id=?");
            $stmt->bind_param("ssssssi", $username, $email, $contact, $role, $status, $department, $id);
        }

        if ($stmt->execute()) {
            $alertMessage = 'User information has been updated successfully.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Failed to update user.';
            $alertType = 'error';
        }
        $stmt->close();
    } else {
        // Add new user
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $contact = trim($_POST['contact']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        $department = trim($_POST['department']);
        $password = $_POST['password'];

        // Check duplicate email
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $alertMessage = "User with email $email already exists.";
            $alertType = 'warning';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username,email,contact,role,status,department,password) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $username, $email, $contact, $role, $status, $department, $hashedPassword);
            if ($stmt->execute()) {
                $alertMessage = 'New user has been added successfully.';
                $alertType = 'success';
            } else {
                $alertMessage = 'Failed to add user.';
                $alertType = 'error';
            }
            $stmt->close();
        }
        $stmtCheck->close();
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

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($alertMessage): ?>
<script>
    Swal.fire({
        icon: '<?= $alertType ?>',
        title: '<?= addslashes($alertMessage) ?>',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        <?php if ($alertType === 'success'): ?>
            window.location = 'User_creation.php';
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<!-- Add/Edit User Form -->
<div class="erp-card mb-4">
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
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="<?= $editUser ? 'edit_username' : 'username' ?>" class="form-control"
                    value="<?= htmlspecialchars($editUser['username'] ?? ($_POST['username'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="<?= $editUser ? 'edit_email' : 'email' ?>" class="form-control"
                    value="<?= htmlspecialchars($editUser['email'] ?? ($_POST['email'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact</label>
                <input type="text" name="<?= $editUser ? 'edit_contact' : 'contact' ?>" class="form-control"
                    pattern="^[0-9]{10}$" maxlength="10"
                    value="<?= htmlspecialchars($editUser['contact'] ?? ($_POST['contact'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= $editUser ? 'New Password (leave blank to keep old)' : 'Password' ?></label>
                <input type="password" name="<?= $editUser ? 'edit_password' : 'password' ?>" class="form-control"
                    <?= $editUser ? '' : 'required' ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <?php $currentRole = strtolower(trim($editUser['role'] ?? ($_POST['role'] ?? ''))); ?>
                <select name="<?= $editUser ? 'edit_role' : 'role' ?>" class="form-control" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="security" <?= $currentRole === 'security' ? 'selected' : '' ?>>Security</option>
                    <option value="host" <?= $currentRole === 'host' ? 'selected' : '' ?>>Host</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Status</label>
                <?php $currentStatus = strtolower(trim($editUser['status'] ?? ($_POST['status'] ?? ''))); ?>
                <select name="<?= $editUser ? 'edit_status' : 'status' ?>" class="form-control" required>
                    <option value="">Select Status</option>
                    <option value="Active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Department</label>
                <input type="text" name="<?= $editUser ? 'edit_department' : 'department' ?>" class="form-control"
                    value="<?= htmlspecialchars($editUser['department'] ?? ($_POST['department'] ?? '')) ?>" required>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="submitbtn"
                class="btn btn-primary"><?= $editUser ? 'Update User' : 'Add User' ?></button>
            <?php if ($editUser): ?>
                <a href="User_creation.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- User Table -->
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
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $row['id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
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

<script>
function deleteUser(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This user will be deleted permanently!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = '?delete=' + id;
        }
    });
}
</script>

<?php echo erp_footer(); ?>
