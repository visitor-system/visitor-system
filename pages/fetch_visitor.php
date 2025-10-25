<?php
require_once '../includes/db.php';

$mobile = $_POST['mobile'] ?? '';
$response = ['found' => false];

if ($mobile) {
  $stmt = $conn->prepare("SELECT visitor_name, company FROM appointments WHERE mobile = ? ORDER BY id DESC LIMIT 1");
  $stmt->bind_param("s", $mobile);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $response = [
      'found' => true,
      'visitor_name' => $row['visitor_name'],
      'company' => $row['company']
    ];
  }
}

echo json_encode($response);
?>
