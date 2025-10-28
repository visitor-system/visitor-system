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

// Get pass and appointment details
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

// Handle check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (empty($pass_data['checkin_time'])) {
        $error_message = "हा visitor अजून check-in केलेला नाही!";
    } else {
        $current_time = date('Y-m-d H:i:s');
        $checkin_time = strtotime($pass_data['checkin_time']);
        $checkout_time = strtotime($current_time);

        $time_spent_seconds = $checkout_time - $checkin_time;
        $hours = floor($time_spent_seconds / 3600);
        $minutes = floor(($time_spent_seconds % 3600) / 60);
        $time_spent_formatted = "{$hours}h {$minutes}m";

        $update_query = "UPDATE passes SET status='out', checkout_time=?, time_spent=? WHERE id=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $current_time, $time_spent_formatted, $pass_id);

        if ($update_stmt->execute()) {
            $success_message = "Visitor checked out successfully!";
            // Refresh pass data
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $pass_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $pass_data = $result->fetch_assoc();
        } else {
            $error_message = "Error updating check-out: " . $conn->error;
        }
    }
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => 'track_visitors.php', 'icon' => 'user-check'],
    ['title' => 'Check Out Visitor', 'url' => '', 'icon' => 'sign-out-alt']
];

echo erp_header('Check Out Visitor', $breadcrumbs);

function formatDateTime($datetime)
{
    return !empty($datetime) && $datetime != '0000-00-00 00:00:00' ? date('d M Y, h:i A', strtotime($datetime)) : '—';
}
?>

<?php if ($success_message): ?>
    <?= erp_alert($success_message, 'success') ?>
    <script>setTimeout(() => window.location.href = 'track_visitors.php', 2000);</script>
<?php endif; ?>
<?php if ($error_message): ?>
    <?= erp_alert($error_message, 'danger') ?>
<?php endif; ?>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-user-times"></i> Visitor Check-Out</h3>
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
                <h5 class="text-warning mb-3">Check-Out Details</h5>

                <?php if ($pass_data['status'] == 'inside'): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="erp-form-label">Current Status</label>
                            <div class="p-2 bg-success text-white rounded"><i class="fas fa-check-circle"></i> Currently
                                Inside</div>
                        </div>


                        <div class="mb-3">
                            <label class="erp-form-label">Check-out Time</label>
                            <input type="text" class="erp-form-control" id="checkout_time" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="erp-form-label">Time Spent</label>
                            <input type="text" class="erp-form-control" id="time_spent" readonly>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="checkout" class="erp-btn erp-btn-warning"><i
                                    class="fas fa-sign-out-alt"></i> Check Out Visitor</button>
                        </div>
                    </form>
                <?php elseif ($pass_data['status'] == 'waiting'): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Not Checked In</h5>
                        <p>This visitor must be checked in first.</p>
                    </div>
                    <a href="checkin.php?id=<?= $pass_id ?>" class="erp-btn erp-btn-success"><i
                            class="fas fa-sign-in-alt"></i> Check In First</a>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> successfully Checked Out</h5>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Update check-out time and time spent dynamically
    function updateCheckoutTime() {
        const checkinTime = new Date("<?= $pass_data['checkin_time'] ?>");
        const now = new Date();

        // Format check-out time
        const options = { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true };
        document.getElementById('checkout_time').value = now.toLocaleString('en-US', options);

        // Calculate time spent
        const diffMs = now - checkinTime;
        const hours = Math.floor(diffMs / 3600000);
        const minutes = Math.floor((diffMs % 3600000) / 60000);
        document.getElementById('time_spent').value = `${hours}h ${minutes}m`;
    }

    updateCheckoutTime();
    setInterval(updateCheckoutTime, 1000); // Update every second
</script>

<?php echo erp_footer(); ?>