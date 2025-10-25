<?php
require '../includes/db.php';

$id = $_POST['id'] ?? '';
$timeSpent = $_POST['time_spent'] ?? '';
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE passes SET status='out', checkout_time=?, time_spent=? WHERE id=?");
$stmt->bind_param("ssi", $now, $timeSpent, $id);
$stmt->execute();

header("Location: track_visitors.php");
exit;
