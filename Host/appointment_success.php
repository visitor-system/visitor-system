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

// -----------------------
// SMS Sending Logic
// -----------------------
$sms_sent = false; // Flag for popup
if ($appointment) {
    $visitor_name = $appointment['visitor_name'];
    $visitor_mobile = $appointment['mobile'];
    $pass_number = $appointment['pass_number'];
    $appointment_time = date('d M Y, h:i A', strtotime($appointment['appointment_time']));

    $sms_message = "Hello $visitor_name! Your appointment is confirmed.\n"
        . "Pass No: $pass_number\n"
        . "Date & Time: $appointment_time\n"
        . "Please reach on time.";

    // Send SMS using Fast2SMS
    $apiKey = "YOUR_FAST2SMS_API_KEY"; // Replace with your API Key
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "sender_id" => "FSTSMS",
            "message" => $sms_message,
            "language" => "english",
            "route" => "v3",
            "numbers" => $visitor_mobile
        ]),
        CURLOPT_HTTPHEADER => array(
            "authorization: $apiKey",
            "accept: application/json",
            "content-type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if (!$err) {
        $sms_sent = true; // SMS sent successfully
    } else {
        error_log("SMS API Error: " . $err);
    }
}

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
                                        <span
                                            class="text-success fw-bold"><?= htmlspecialchars($appointment['pass_number']) ?></span>
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


<!-- QR Code Script -->
<script>
    var qrContainer = document.getElementById("passQR");
    qrContainer.innerHTML = "";

    <?php if ($appointment): ?>
        // Prepare full appointment info for QR code
        var qrText = `
    Visitor Name: <?= htmlspecialchars($appointment['visitor_name']) ?>
    Mobile: <?= htmlspecialchars($appointment['mobile']) ?>
    Company: <?= htmlspecialchars($appointment['company']) ?>
    Whom to Meet: <?= htmlspecialchars($appointment['whom_to_meet']) ?>
    Host Name: <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Host') ?>
    Purpose: <?= htmlspecialchars($appointment['purpose']) ?>
    Appointment Date & Time: <?= date('d M Y, h:i A', strtotime($appointment['appointment_time'])) ?>
    Number of People: <?= $appointment['num_of_people'] ?>
    Pass Number: <?= htmlspecialchars($appointment['pass_number']) ?>
    Status: Waiting
            `;

        new QRCode(qrContainer, {
            text: qrText,
            width: 150,
            height: 150
        });
    <?php endif; ?>


</script>


<?php echo erp_footer(); ?>