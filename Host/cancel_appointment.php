<?php
require_once '../includes/db.php';
$id = $_GET['id'] ?? '';

if ($id) {
  $cancel = $conn->query("UPDATE passes SET status='cancelled' WHERE id='$id'");
  if ($cancel) {
    header("Location: dashboard.php");
    exit;
  }
}
?>