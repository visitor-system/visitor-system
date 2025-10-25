<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pass_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_message = '';
$error_message = '';

// Get pass and appointment details
$query = "SELECT p.*, a.visitor_name, a.company, a.purpose, a.appointment_time, a.mobile, a.whom_to_meet, u.username as host_name
          FROM passes p
          JOIN appointments a ON p.appointment_id = a.id
          LEFT JOIN users u ON a.host_id = u.id
          WHERE p.id = ?";

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Debug: Check if tables exist
$tables_check = $conn->query("SHOW TABLES LIKE 'passes'");
if (!$tables_check || $tables_check->num_rows == 0) {
    die("Error: 'passes' table does not exist. Please run the database setup first.");
}

$tables_check2 = $conn->query("SHOW TABLES LIKE 'appointments'");
if (!$tables_check2 || $tables_check2->num_rows == 0) {
    die("Error: 'appointments' table does not exist. Please run the database setup first.");
}

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $pass_id);
$stmt->execute();
$result = $stmt->get_result();
$pass_data = $result->fetch_assoc();

if (!$pass_data) {
    header("Location: track_visitors.php");
    exit;
}

// Handle check-in form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
    $current_time = date('Y-m-d H:i:s');

    // Update pass status and check-in time
    $update_query = "UPDATE passes SET status = 'inside', checkin_time = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);

    if (!$update_stmt) {
        $error_message = "Prepare failed for update: " . $conn->error;
    } else {
        $update_stmt->bind_param("si", $current_time, $pass_id);

        if ($update_stmt->execute()) {
            $success_message = "Visitor checked in successfully!";

            // Refresh data
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $pass_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $pass_data = $result->fetch_assoc();
            }
        } else {
            $error_message = "Error checking in visitor: " . $conn->error;
        }
    }
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => 'track_visitors.php', 'icon' => 'user-check'],
    ['title' => 'Check In Visitor', 'url' => '', 'icon' => 'sign-in-alt']
];

echo erp_header('Check In Visitor', $breadcrumbs);
?>

<!-- Alerts -->
<?php if ($success_message): ?>
    <?php echo erp_alert($success_message, 'success'); ?>
    <script>
        setTimeout(() => {
            window.location.href = 'track_visitors.php';
        }, 3000);
    </script>
<?php endif; ?>

<?php if ($error_message): ?>
    <?php echo erp_alert($error_message, 'danger'); ?>
<?php endif; ?>

<!-- Visitor Information -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-user-check"></i>
            Visitor Check-In
        </h3>
    </div>
    <div class="erp-card-body">
        <div class="row g-4">
            <!-- Visitor Details -->
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Visitor Information</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <strong>Pass Number:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['pass_number']) ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Visitor Name:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['visitor_name']) ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Company:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['company']) ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Mobile:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['mobile']) ?></span>
                    </div>
                    <div class="col-12">
                        <strong>Purpose:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['purpose']) ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Whom to Meet:</strong><br>
                        <span class="text-primary"><?= htmlspecialchars($pass_data['whom_to_meet']) ?></span>
                    </div>
                    <div class="col-6">
                        <strong>Host Name:</strong><br>
                        <span class="text-success"><?= htmlspecialchars($pass_data['host_name']) ?></span>
                    </div>
                    <div class="col-12">
                        <strong>Appointment Time:</strong><br>
                        <span
                            class="text-primary"><?= date('d M Y, h:i A', strtotime($pass_data['appointment_time'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Check-in Form -->
            <div class="col-md-6">
                <h5 class="text-success mb-3">Check-In Details</h5>

                <?php if ($pass_data['status'] == 'waiting'): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="erp-form-label">Current Status</label>
                            <div class="p-2 bg-warning text-dark rounded">
                                <i class="fas fa-clock"></i> Waiting for Check-in
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="erp-form-label">Check-in Time</label>
                            <input type="text" class="erp-form-control" value="<?= date('d M Y, h:i A') ?>" readonly>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="checkin" class="erp-btn erp-btn-success">
                                <i class="fas fa-sign-in-alt"></i> Check In Visitor
                            </button>
                        </div>
                    </form>
                <?php elseif ($pass_data['status'] == 'inside'): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Already Checked In</h5>
                        <p class="mb-2"><strong>Check-in Time:</strong>
                            <?= date('d M Y, h:i A', strtotime($pass_data['checkin_time'])) ?></p>
                        <p class="mb-0">This visitor is currently inside the premises.</p>
                    </div>

                    <div class="d-grid">
                        <a href="checkout.php?id=<?= $pass_id ?>" class="erp-btn erp-btn-warning">
                            <i class="fas fa-sign-out-alt"></i> Check Out Visitor
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Visit Completed</h5>
                        <p class="mb-2"><strong>Check-in Time:</strong>
                            <?= date('d M Y, h:i A', strtotime($pass_data['checkin_time'])) ?></p>
                        <p class="mb-2"><strong>Check-out Time:</strong>
                            <?= date('d M Y, h:i A', strtotime($pass_data['checkout_time'])) ?></p>
                        <p class="mb-0">This visitor has already completed their visit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row g-3 mt-4">
    <div class="col-md-6">
        <?php echo erp_link_button('Back to Visitor Tracking', 'track_visitors.php', 'secondary', '', 'fas fa-arrow-left'); ?>
    </div>
    <div class="col-md-6">
        <?php echo erp_link_button('View All Visitors', 'track_visitors.php', 'primary', '', 'fas fa-users'); ?>
    </div>
</div>

<?php echo erp_footer(); ?>