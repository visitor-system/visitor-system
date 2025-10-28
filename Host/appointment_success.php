<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.html");
    exit;
}

// Get the latest appointment for this user
$stmt = $conn->prepare("SELECT a.*, p.pass_number, p.qr_code FROM appointments a 
                       LEFT JOIN passes p ON a.id = p.appointment_id 
                       WHERE a.host_id = ? 
                       ORDER BY a.id DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Book Appointment', 'url' => 'book_appointment.php', 'icon' => 'calendar-plus'],
    ['title' => 'Appointment Success', 'url' => '', 'icon' => 'check-circle']
];

echo erp_header('Appointment Success', $breadcrumbs);
?>

<script src="../includes/js/qrcode.min.js"></script>

<!-- Success Message -->
<div class="erp-card text-center">
    <div class="erp-card-body py-5">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
        </div>
        <h2 class="text-success mb-3">Appointment Booked Successfully!</h2>
        <p class="text-muted mb-4">Your appointment has been confirmed and a visitor pass has been generated.</p>

        <?php if ($appointment): ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <h5 class="erp-card-title">
                                <i class="fas fa-calendar-check"></i>
                                Appointment Details
                            </h5>
                        </div>
                        <div class="erp-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Visitor Name:</strong><br>
                                    <span class="text-primary"><?= htmlspecialchars($appointment['visitor_name']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Mobile:</strong><br>
                                    <span class="text-primary"><?= htmlspecialchars($appointment['mobile']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Company:</strong><br>
                                    <span class="text-primary"><?= htmlspecialchars($appointment['company']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Whom to Meet:</strong><br>
                                    <span class="text-primary"><?= htmlspecialchars($appointment['whom_to_meet']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Host Name:</strong><br>
                                    <span
                                        class="text-success"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Host') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Purpose:</strong><br>
                                    <span class="text-primary"><?= htmlspecialchars($appointment['purpose']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Appointment Date & Time:</strong><br>
                                    <span
                                        class="text-primary"><?= date('d M Y, h:i A', strtotime($appointment['appointment_time'])) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Number of People:</strong><br>
                                    <span class="text-primary"><?= $appointment['num_of_people'] ?></span>
                                </div>
                                <?php if ($appointment['pass_number']): ?>
                                    <div class="col-md-6">
                                        <strong>Pass Number:</strong><br>
                                        <div id="passQR"
                                            style="padding: 5px; display: flex; justify-content: center; align-items: center;">
                                        </div>
                                        <span class="text-success fw-bold"><?= htmlspecialchars($appointment['pass_number']) ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong><br>
                                        <?php echo erp_badge('Waiting', 'warning'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <?php echo erp_link_button('Book Another Appointment', 'book_appointment.php', 'primary', '', 'fas fa-calendar-plus'); ?>
            <?php echo erp_link_button('View All Appointments', 'book_appointment.php', 'secondary', '', 'fas fa-list'); ?>
            <?php echo erp_link_button('Back to Dashboard', 'dashboard.php', 'info', '', 'fas fa-tachometer-alt'); ?>
        </div>
    </div>
</div>

<script>
    var qrContainer = document.getElementById("passQR");
    qrContainer.innerHTML = "";

    var MCode = '<?= htmlspecialchars($appointment['pass_number']) ?>';
    if (MCode != "") {
        new QRCode(qrContainer, {
            text: MCode,
            width: 100,
            height: 100
        });
    }
</script>

<?php echo erp_footer(); ?>