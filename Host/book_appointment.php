<?php
session_start();
require_once '../includes/db.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/erp_layout.php';

date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.html");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_appointment'])) {
    $visitor_name = trim($_POST['visitor_name']);
    $mobile = trim($_POST['mobile']);
    $company = trim($_POST['company']);
    $whom_to_meet = trim($_POST['whom_to_meet']);
    $purpose = trim($_POST['purpose']);
    $num_of_people = intval($_POST['num_of_people']);
    $appointment_time = $_POST['appointment_time'];
    $edit_id = intval($_POST['edit_id'] ?? 0);

    // Validate mobile
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        echo erp_alert("Mobile number must be exactly 10 digits.", 'danger');
        exit;
    }

    // Validate date (should not be past)
    $currentDateTime = date('Y-m-d H:i:s');
    if (strtotime($appointment_time) < strtotime($currentDateTime)) {
        echo erp_alert("Appointment time cannot be in the past.", 'danger');
        exit;
    }

    if ($edit_id > 0) {
        // Update appointment
        $stmt = $conn->prepare("UPDATE appointments SET visitor_name=?, mobile=?, company=?, whom_to_meet=?, purpose=?, num_of_people=?, appointment_time=? WHERE id=?");
        $stmt->bind_param("sssssisi", $visitor_name, $mobile, $company, $whom_to_meet, $purpose, $num_of_people, $appointment_time, $edit_id);
        $stmt->execute();
        $stmt->close();
        header("Location: appointment_success.php");
        exit;
    } else {
        // Insert new appointment
        $stmt = $conn->prepare("INSERT INTO appointments (visitor_name, mobile, company, whom_to_meet, purpose, num_of_people, appointment_time, status, host_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("sssssisi", $visitor_name, $mobile, $company, $whom_to_meet, $purpose, $num_of_people, $appointment_time, $_SESSION['user']['id']);
        $stmt->execute();
        $appointment_id = $conn->insert_id;
        $stmt->close();

        // Generate pass number and QR code
        $pass_number = 'VP' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT);
        $qrDir = '../assets/qrcodes/';
        if (!is_dir($qrDir))
            mkdir($qrDir, 0777, true);
        $qrPath = $qrDir . $pass_number . '.png';
        QRcode::png('Visitor Pass ID: ' . $pass_number, $qrPath, QR_ECLEVEL_L, 4);

        // Insert pass
        $stmt2 = $conn->prepare("INSERT INTO passes (appointment_id, pass_number, qr_code, status) VALUES (?, ?, ?, 'waiting')");
        $stmt2->bind_param("iss", $appointment_id, $pass_number, $qrPath);
        $stmt2->execute();
        $stmt2->close();

        header("Location: appointment_success.php");
        exit;
    }
}

// Edit existing appointment
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id=? AND host_id=?");
    $stmt->bind_param("ii", $edit_id, $_SESSION['user']['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res->fetch_assoc();
    $stmt->close();
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Book Appointment', 'icon' => 'calendar-plus']
];

echo erp_header('Book Appointment', $breadcrumbs);
?>

<!-- Appointment Form -->
<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h5 class="erp-card-title"><i
                class="fas fa-calendar-plus me-2"></i><?= $editData ? "Edit Appointment" : "Book New Appointment" ?>
        </h5>
    </div>
    <div class="erp-card-body">
        <form method="POST" id="appointmentForm">
            <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? '' ?>">
            <input type="hidden" name="save_appointment" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile" required
                        value="<?= htmlspecialchars($editData['mobile'] ?? '') ?>">
                    <div id="mobileError" class="text-danger mt-1" style="display:none;">Mobile number cannot exceed 10
                        digits</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="visitor_name" class="form-control" placeholder="Visitor Name" required
                        value="<?= htmlspecialchars($editData['visitor_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control" placeholder="Company" required
                        value="<?= htmlspecialchars($editData['company'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Host Name (You)</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($_SESSION['user']['name'] ?? 'Host') ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Whom to Meet</label>
                    <input type="text" name="whom_to_meet" class="form-control"
                        placeholder="Person visitor wants to meet" required
                        value="<?= htmlspecialchars($editData['whom_to_meet'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Purpose of Visit</label>
                    <textarea name="purpose" class="form-control" placeholder="Purpose" required
                        rows="3"><?= htmlspecialchars($editData['purpose'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Number of People</label>
                    <input type="number" name="num_of_people" class="form-control" min="1"
                        placeholder="Number of People" required
                        value="<?= htmlspecialchars($editData['num_of_people'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Appointment Date & Time</label>
                    <?php
                    $appt_time = isset($editData['appointment_time'])
                        ? date('Y-m-d\TH:i', strtotime($editData['appointment_time']))
                        : date('Y-m-d\TH:i');
                    ?>
                    <input type="datetime-local" name="appointment_time" class="form-control" required
                        min="<?= date('Y-m-d\TH:i') ?>" value="<?= $appt_time ?>">
                </div>
            </div>
            <div class="mt-4 text-end">
                <?= erp_button($editData ? 'Update Appointment' : 'Confirm Appointment', 'primary', '', 'fas fa-save', 'type="submit"'); ?>
            </div>
        </form>
    </div>
</div>

<!-- JS for Mobile Validation -->
<script>
    const mobileInput = document.getElementById('mobile');
    const mobileError = document.getElementById('mobileError');

    mobileInput.addEventListener('input', () => {
        if (mobileInput.value.length > 10) {
            mobileError.style.display = 'block';
            mobileInput.value = mobileInput.value.slice(0, 10); // prevent more than 10 digits
        } else {
            mobileError.style.display = 'none';
        }
    });
</script>

<?php echo erp_footer(); ?>