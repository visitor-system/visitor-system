<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/erp_layout.php';
require_once '../vendor/autoload.php'; // PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// -----------------------
// Email Sending Logic
// -----------------------
// $email_sent = false;
// if ($appointment) {
//     $mail = new PHPMailer(true);
//     try {
//         //Server settings
//         $mail->isSMTP();
//         $mail->Host = 'smtp.yourmailserver.com'; // Change this to your organization's SMTP server
//         $mail->SMTPAuth = true;
//         $mail->Username = 'your_email@yourdomain.com'; // Your email address
//         $mail->Password = 'your_email_password'; // Your email password or app password
//         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//         $mail->Port = 587;

//         //Recipients
//         $mail->setFrom('your_email@yourdomain.com', 'Your Organization');
//         $mail->addAddress($appointment['visitor_email'], $appointment['visitor_name']); // Recipient email

//         // Content
//         $mail->isHTML(true);
//         $mail->Subject = 'Appointment Confirmation';
//         $mail->Body = "
//             <h3>Hello {$appointment['visitor_name']},</h3>
//             <p>Your appointment has been successfully booked.</p>
//             <p><strong>Appointment Details:</strong></p>
//             <ul>
//                 <li><strong>Pass No:</strong> {$appointment['pass_number']}</li>
//                 <li><strong>Visitor Name:</strong> {$appointment['visitor_name']}</li>
//                 <li><strong>Mobile:</strong> {$appointment['mobile']}</li>
//                 <li><strong>Company:</strong> {$appointment['company']}</li>
//                 <li><strong>Purpose:</strong> {$appointment['purpose']}</li>
//                 <li><strong>Appointment Date & Time:</strong> {$appointment_time}</li>
//             </ul>
//             <p>Please reach on time. We look forward to seeing you!</p>
//             <p>Best regards,<br>Your Organization</p>
//         ";
//         $mail->AltBody = "Hello {$appointment['visitor_name']}, your appointment details are as follows:\n"
//             . "Pass No: {$appointment['pass_number']}\n"
//             . "Visitor Name: {$appointment['visitor_name']}\n"
//             . "Mobile: {$appointment['mobile']}\n"
//             . "Company: {$appointment['company']}\n"
//             . "Purpose: {$appointment['purpose']}\n"
//             . "Date & Time: {$appointment_time}\n\n"
//             . "Please reach on time. We look forward to seeing you!\n\nBest regards, Your Organization";

//         $mail->send();
//         $email_sent = true;
//     } catch (Exception $e) {
//         error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
//     }
// }

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
                                    <span id="visitor_name" class="text-primary"><?= htmlspecialchars($appointment['visitor_name']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Mobile:</strong><br>
                                    <span id="Mobile" class="text-primary"><?= htmlspecialchars($appointment['mobile']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Company:</strong><br>
                                    <span id="company"class="text-primary"><?= htmlspecialchars($appointment['company']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Whom to Meet:</strong><br>
                                    <span id="whom_to_meet" class="text-primary"><?= htmlspecialchars($appointment['whom_to_meet']) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Host Name:</strong><br>
                                    <span id="Host_name"
                                        class="text-success"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Host') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Purpose:</strong><br>
                                    <span id="Purpose"class="text-primary"><?= htmlspecialchars($appointment['purpose']) ?></span>
                                </div>

                                <div class="col-md-6">
                                    <strong>Appointment Date_Time:</strong><br>
                                    <span id="Appointment_Date_Time:"
                                        class="text-primary"><?= date('d M Y, h:i A', strtotime($appointment['appointment_time'])) ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Number of People:</strong><br>
                                    <span id="Number_of_people"class="text-primary"><?= $appointment['num_of_people'] ?></span>
                                </div>
                                 <div class="col-md-6">
                                    <strong>Email</strong><br>
                                    <span id="Email"class="text-primary"><?= htmlspecialchars($appointment['Email']) ?></span>
                                </div>
                                 <div class="col-md-6">
                                 <strong>Department</strong><br>
                                 <span id="Department" class="text-primary"><?= htmlspecialchars($appointment['Department']) ?></span>
                                
                                </div>

                                <?php if ($appointment['pass_number']): ?>
                                    <div class="col-md-6">
                                        <strong>Pass Number:</strong><br>
                                        <div id="passQR"
                                            style="padding: 5px; display: flex; justify-content: center; align-items: center;">
                                        </div>
                                        <span id="Pass_Number"
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
            <?php echo erp_link_button('View All Appointments', 'book_appointment.php', 'primary', '', 'fas fa-list'); ?>
          <button  id= 'email_button' class='erp-btn erp-btn-primary' onclick="SendEmail()"><i class='fas fa-calendar-plus'></i>Send Email</button>
            <?php echo erp_link_button('Back to Dashboard', 'dashboard.php', 'info', '', 'fas fa-tachometer-alt'); ?>
           
        </div>
    </div>
</div>

<script>
    function SendEmail() {
        document.getElementById('email_button').disabled = true;
  // Get values from inputs using IDs
  const name =document.getElementById("visitor_name").textContent.trim();
  const email =document.getElementById("Email").textContent.trim();
  const Mobile =document.getElementById("Mobile").textContent.trim();
  const company =document.getElementById("company").textContent.trim();
  const whom_to_meet =document.getElementById("whom_to_meet").textContent.trim();
  const Host_name = document.getElementById("Host_name").textContent.trim();
  const Purpose = document.getElementById("Purpose").textContent.trim();
  
  const Appointment_Date_Time=document.getElementById("Appointment_Date_Time:").textContent.trim();
  const Number_of_People = document.getElementById("Number_of_people").textContent.trim();
  const Pass_Number = document.getElementById("Pass_Number").textContent.trim();
 


  // Basic validation
  if (!name) {
    alert("Please fill all fields before sending!");
    return;
  }

  // Prepare data
  const formData = new FormData();
  formData.append("name", name);
  formData.append("email", email);
  formData.append("Mobile", Mobile);
  formData.append("company", company);
  formData.append("whom_to_meet",whom_to_meet );
  formData.append("Host_name", Host_name);
  formData.append("Purpose", Purpose);
  formData.append("Appointment_Date_Time", Appointment_Date_Time);
  formData.append("Number_of_People", Number_of_People);
  formData.append("Pass_Number",Pass_Number);

  // Send to PHP using Fetch
  fetch("appointment_Email.php", {
    method: "POST",
    body: formData
  })
    .then(response => response.text())
    .then(data => {
      alert(data); // Show the message returned by PHP
      document.getElementById('email_button').disabled = true;
    })
    .catch(error => {
        document.getElementById('email_button').disabled = false;
      console.error("Error:", error);
      alert("Something went wrong while sending email.");
    });
}
</script>

<!-- QR Code Script -->
<script>
    var qrContainer = document.getElementById("passQR");
    qrContainer.innerHTML = "";

    <?php if ($appointment): ?>
        // Prepare full appointment info for QR code
        var qrText = `Visitor Name: <?= htmlspecialchars($appointment['visitor_name']) ?>
    Mobile: <?= htmlspecialchars($appointment['mobile']) ?>
    Company: <?= htmlspecialchars($appointment['company']) ?>
    Whom to Meet: <?= htmlspecialchars($appointment['whom_to_meet']) ?>
    Host Name: <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Host') ?>
    Purpose: <?= htmlspecialchars($appointment['purpose']) ?>
    Appointment Date & Time: <?= date('d M Y, h:i A', strtotime($appointment['appointment_time'])) ?>
    Number of People: <?= $appointment['num_of_people'] ?>
    Pass Number: <?= htmlspecialchars($appointment['pass_number']) ?>
    Status: Waiting`;

        new QRCode(qrContainer, {
            text: qrText,
            width: 150,
            height: 150
        });
    <?php endif; ?>
</script>

<?php echo erp_footer(); ?>
