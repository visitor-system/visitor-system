<?php
session_start();
require_once '../includes/db.php';
require_once '../phpqrcode/qrlib.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'host') {
  header("Location: login.php");
  exit;
}

// Get form data safely
$visitor_name = trim($_POST['visitor_name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$company = trim($_POST['company'] ?? '');
$whom_to_meet = trim($_POST['whom_to_meet'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');
$appointment_time = $_POST['appointment_time'] ?? '';
$host_id = $_SESSION['user']['id'];

// Validate required fields
if (!$visitor_name || !$mobile || !$company || !$whom_to_meet || !$purpose || !$appointment_time) {
  die("All fields are required.");
}

// Insert into appointments
$stmt = $conn->prepare("INSERT INTO appointments (visitor_name, mobile, company, host_id, whom_to_meet, purpose, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
if (!$stmt) {
  die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ssissss", $visitor_name, $mobile, $company, $host_id, $whom_to_meet, $purpose, $appointment_time);
if (!$stmt->execute()) {
  die("Execute failed: " . $stmt->error);
}
$appointment_id = $conn->insert_id;
$stmt->close();

// Generate Visitor Pass ID
$visitor_id = "VP" . str_pad($appointment_id, 5, "0", STR_PAD_LEFT);

// Generate QR Code
$folder = '../assets/qrcodes/';
if (!file_exists($folder)) {
  mkdir($folder, 0777, true);
}
$filePath = $folder . $visitor_id . '.png';
QRcode::png("Visitor Pass ID: " . $visitor_id, $filePath, QR_ECLEVEL_L, 4);

// Insert into passes table
$stmt2 = $conn->prepare("INSERT INTO passes (appointment_id, pass_number, qr_code, status) VALUES (?, ?, ?, 'waiting')");
if (!$stmt2) {
  die("Prepare failed: " . $conn->error);
}
$stmt2->bind_param("iss", $appointment_id, $visitor_id, $filePath);
if (!$stmt2->execute()) {
  die("Execute failed: " . $stmt2->error);
}
$stmt2->close();

// Optionally: Send SMS to Host and Visitor (Replace with real SMS API)
$hostQuery = $conn->prepare("SELECT mobile FROM users WHERE id = ?");
$hostQuery->bind_param("i", $host_id);
$hostQuery->execute();
$hostResult = $hostQuery->get_result();
$host = $hostResult->fetch_assoc();
$host_mobile = $host['mobile'] ?? '';

$message = "Appointment Confirmed!\nVisitor: $visitor_name\nCompany: $company\nTime: $appointment_time\nPass ID: $visitor_id";

// Example: file_get_contents("https://sms-api.example.com/send?to=$mobile&msg=" . urlencode($message));
// Example: file_get_contents("https://sms-api.example.com/send?to=$host_mobile&msg=" . urlencode($message));

// Redirect to success page
header("Location: appointment_success.php?id=" . urlencode($visitor_id));
exit;
?>