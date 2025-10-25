<?php
session_start();
require '../includes/db.php';
date_default_timezone_set('Asia/Kolkata'); // Set to IST

// Validate session and role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'security') {
  header("Location: login.html");
  exit;
}

// Get pass ID
$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
  die("Invalid visitor ID.");
}

// Fetch check-in time
$result = $conn->query("SELECT checkin_time FROM passes WHERE id='$id'");
$row = $result->fetch_assoc();

if ($row && !empty($row['checkin_time'])) {
  $checkin = new DateTime($row['checkin_time']);
  $checkout = new DateTime(); // current time
  $interval = $checkin->diff($checkout);

  // ✅ Format time spent as "1h 22m"
  $hours = $interval->h;
  $minutes = $interval->i;
  $timeSpent = "{$hours}h {$minutes}m";

  $checkoutTime = $checkout->format('Y-m-d H:i:s');

  // Update pass record
  $update = $conn->query("UPDATE passes SET status='out', checkout_time='$checkoutTime', time_spent='$timeSpent' WHERE id='$id'");

  if ($update) {
    header("Location: track_visitors.php");
    exit;
  } else {
    echo "<div class='alert alert-danger'>Error updating checkout: " . $conn->error . "</div>";
  }
} else {
  echo "<div class='alert alert-warning'>Check-in time not found for this visitor.</div>";
}
?>