<?php
session_start();
require '../includes/db.php';

// Filename
$filename = "visitor_pass_report_" . date("Y-m-d_H-i") . ".excel";

// Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Start output
echo "<table border='1'>";
echo "<tr><th colspan='16' style='font-size:16pt;'>Company Name: PDVS</th></tr>";
echo "<tr><th colspan='16'>Address: Kolhapur</th></tr>";
echo "<tr><th colspan='16'>Generated On: " . date("d M Y, H:i") . "</th></tr>";
echo "<tr></tr>"; // blank row

// Column headers
$headers = [
  'Pass No',
  'Visitor Name',
  'Mobile',
  'Company',
  'Purpose',
  'Whom to Meet',
  'No. of People',
  'Host',
  'Appointment Date',
  'Appointment Time',
  'Check-in Date',
  'Check-in Time',
  'Check-out Date',
  'Check-out Time',
  'Time Spent',
  'Status'
];
echo "<tr>";
foreach ($headers as $header) {
  echo "<th>$header</th>";
}
echo "</tr>";

// Filters
$where = "WHERE 1=1";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
  $from = $conn->real_escape_string($_GET['from']);
  $to = $conn->real_escape_string($_GET['to']);
  $where .= " AND DATE(p.checkin_time) BETWEEN '$from' AND '$to'";
}
if (!empty($_GET['company'])) {
  $company = $conn->real_escape_string($_GET['company']);
  $where .= " AND a.company LIKE '%$company%'";
}
if (!empty($_GET['host'])) {
  $host = $conn->real_escape_string($_GET['host']);
  $where .= " AND u.username LIKE '%$host%'";
}
if (!empty($_GET['status'])) {
  $status = $conn->real_escape_string($_GET['status']);
  $where .= " AND p.status = '$status'";
}
if (!empty($_GET['search'])) {
  $s = $conn->real_escape_string($_GET['search']);
  $where .= " AND (a.visitor_name LIKE '%$s%' OR a.mobile LIKE '%$s%' OR a.company LIKE '%$s%' OR p.pass_number LIKE '%$s%')";
}

// Fetch data
$query = "SELECT p.*, a.visitor_name, a.mobile, a.company, a.purpose, a.whom_to_meet, a.num_of_people, a.appointment_time, u.username AS host_name
          FROM passes p
          JOIN appointments a ON p.appointment_id = a.id
          JOIN users u ON a.host_id = u.id
          $where
          ORDER BY p.id DESC";

$result = $conn->query($query);
if (!$result) {
  die('Database query error: ' . $conn->error);
}

// Output rows
while ($r = $result->fetch_assoc()) {
  $apptDate = $r['appointment_time'] ? date("d M Y", strtotime($r['appointment_time'])) : '';
  $apptTime = $r['appointment_time'] ? date("H:i", strtotime($r['appointment_time'])) : '';
  $checkinDate = $r['checkin_time'] ? date("d M Y", strtotime($r['checkin_time'])) : '';
  $checkinTime = $r['checkin_time'] ? date("H:i", strtotime($r['checkin_time'])) : '';
  $checkoutDate = $r['checkout_time'] ? date("d M Y", strtotime($r['checkout_time'])) : '';
  $checkoutTime = $r['checkout_time'] ? date("H:i", strtotime($r['checkout_time'])) : '';

  $timeSpent = '';
  if ($r['checkin_time'] && $r['checkout_time']) {
    $diff = strtotime($r['checkout_time']) - strtotime($r['checkin_time']);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $timeSpent = ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
  }

  $status = '';
  if ($r['status'] === 'out' && $r['checkin_time'] && $r['checkout_time']) {
    $status = 'Completed';
  } elseif ($r['status']) {
    $status = ucfirst($r['status']);
  }

  $mobile = $r['mobile'] ? $r['mobile'] : '';

  echo "<tr>";
  echo "<td>{$r['pass_number']}</td>";
  echo "<td>{$r['visitor_name']}</td>";
  echo "<td>'$mobile</td>"; // Excel treat as text
  echo "<td>{$r['company']}</td>";
  echo "<td>{$r['purpose']}</td>";
  echo "<td>{$r['whom_to_meet']}</td>";
  echo "<td>{$r['num_of_people']}</td>";
  echo "<td>{$r['host_name']}</td>";
  echo "<td>$apptDate</td>";
  echo "<td>$apptTime</td>";
  echo "<td>$checkinDate</td>";
  echo "<td>$checkinTime</td>";
  echo "<td>$checkoutDate</td>";
  echo "<td>$checkoutTime</td>";
  echo "<td>$timeSpent</td>";
  echo "<td>$status</td>";
  echo "</tr>";
}

echo "</table>";
exit;
?>