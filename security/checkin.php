<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pass_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_message = '';
$error_message = '';

// Handle check-in POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
    $current_time = date('Y-m-d H:i:s');
    $update_query = "UPDATE passes SET status='inside', checkin_time=? WHERE id=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $current_time, $pass_id);
    if ($stmt->execute()) {
        $success_message = "Visitor checked in successfully!";
    } else {
        $error_message = "Check-in failed: " . $conn->error;
    }
}

// Fetch visitor pass & appointment
$query = "SELECT p.*, a.visitor_name, a.company, a.purpose, a.appointment_time, a.mobile, a.whom_to_meet, u.username as host_name
          FROM passes p
          JOIN appointments a ON p.appointment_id = a.id
          LEFT JOIN users u ON a.host_id = u.id
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $pass_id);
$stmt->execute();
$result = $stmt->get_result();
$pass_data = $result->fetch_assoc();

if (!$pass_data) {
    echo "<script>window.close();</script>";
    exit;
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => 'track_visitors.php', 'icon' => 'user-check'],
    ['title' => 'Check In Visitor', 'url' => '', 'icon' => 'sign-in-alt']
];

echo erp_header('Check In Visitor', $breadcrumbs);

function formatDateTime($dt)
{
    return (!empty($dt) && $dt != '0000-00-00 00:00:00') ? date('d M Y, h:i A', strtotime($dt)) : '—';
}
?>

<?php if ($success_message): ?>
    <?= erp_alert($success_message, 'success') ?>
    <script>
        // Apply same auto-back as checkout: 2 seconds
        setTimeout(() => {
            window.location.href = 'track_visitors.php';
        }, 2000);
    </script>
<?php endif; ?>
<?php if ($error_message): ?>
    <?= erp_alert($error_message, 'danger') ?>
<?php endif; ?>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-user-check"></i> Visitor Check-In</h3>
    </div>
    <div class="erp-card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Visitor Information</h5>
                <div class="row g-3">
                    <div class="col-6"><strong>Pass Number:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['pass_number']) ?></span></div>
                    <div class="col-6"><strong>Visitor Name:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['visitor_name']) ?></span></div>
                    <div class="col-6"><strong>Company:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['company']) ?></span></div>
                    <div class="col-6"><strong>Mobile:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['mobile']) ?></span></div>
                    <div class="col-12"><strong>Purpose:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['purpose']) ?></span></div>
                    <div class="col-6"><strong>Whom to Meet:</strong><br><span
                            class="text-primary"><?= htmlspecialchars($pass_data['whom_to_meet']) ?></span></div>
                    <div class="col-6"><strong>Host Name:</strong><br><span
                            class="text-success"><?= htmlspecialchars($pass_data['host_name']) ?></span></div>
                    <div class="col-12"><strong>Appointment Time:</strong><br><span
                            class="text-primary"><?= formatDateTime($pass_data['appointment_time']) ?></span></div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="text-success mb-3">Check-In Details</h5>
                <?php if ($pass_data['status'] == 'waiting'): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="erp-form-label">Current Status</label>
                            <div class="p-2 bg-warning text-dark rounded"><i class="fas fa-clock"></i> Waiting for Check-in
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="erp-form-label">Check-in Time</label>
                            <input type="text" id="checkin_time" class="erp-form-control" readonly>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="checkin" class="erp-btn erp-btn-success"><i
                                    class="fas fa-sign-in-alt"></i> Check In Visitor</button>
                        </div>
                    </form>
                <?php elseif ($pass_data['status'] == 'inside'): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> successfully Checked In</h5>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Visit Completed</h5>
                        <p><strong>Check-in Time:</strong> <?= formatDateTime($pass_data['checkin_time']) ?></p>
                        <p><strong>Check-out Time:</strong> <?= formatDateTime($pass_data['checkout_time']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically fill the current datetime in Check-in Time field
    function updateCurrentTime() {
        const now = new Date();
        const options = { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true };
        document.getElementById('checkin_time').value = now.toLocaleString('en-US', options);
    }
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000); // update every second
</script>

<?php echo erp_footer(); ?>