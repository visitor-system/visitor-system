<?php
session_start();
require '../includes/db.php';

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=visitor_pass_report.csv');

$output = fopen('php://output', 'w');

// Company header info
fputcsv($output, ["Company Name: PDVS"]);
fputcsv($output, ["Address: Kolhapur"]);
fputcsv($output, ["Generated On: " . date("d M Y, H:i")]);
fputcsv($output, []); // blank row

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
fputcsv($output, $headers);

// Filters
$where = "WHERE 1=1";

if (!empty($_GET['from']) && !empty($_GET['to'])) {
  $from = $_GET['from'];
  $to = $_GET['to'];
  $where .= " AND DATE(p.checkin_time) BETWEEN '$from' AND '$to'";
}
if (!empty($_GET['company']))
  $where .= " AND a.company LIKE '%" . $conn->real_escape_string($_GET['company']) . "%'";
if (!empty($_GET['host']))
  $where .= " AND u.username LIKE '%" . $conn->real_escape_string($_GET['host']) . "%'";
if (!empty($_GET['status']))
  $where .= " AND p.status = '" . $conn->real_escape_string($_GET['status']) . "'";
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

// Output rows
while ($r = $result->fetch_assoc()) {
  // Appointment
  $apptDate = $r['appointment_time'] ? date("d M Y", strtotime($r['appointment_time'])) : '';
  $apptTime = $r['appointment_time'] ? date("H:i", strtotime($r['appointment_time'])) : '';

  // Check-in
  $checkinDate = $r['checkin_time'] ? date("d M Y", strtotime($r['checkin_time'])) : '';
  $checkinTime = $r['checkin_time'] ? date("H:i", strtotime($r['checkin_time'])) : '';

  // Check-out
  $checkoutDate = $r['checkout_time'] ? date("d M Y", strtotime($r['checkout_time'])) : '';
  $checkoutTime = $r['checkout_time'] ? date("H:i", strtotime($r['checkout_time'])) : '';

  // Time spent
  $timeSpent = '';
  if ($r['checkin_time'] && $r['checkout_time']) {
    $diff = strtotime($r['checkout_time']) - strtotime($r['checkin_time']);
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $timeSpent = ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
  }

  // Status
  $status = '';
  if ($r['status'] === 'out' && $r['checkin_time'] && $r['checkout_time']) {
    $status = 'Completed';
  } elseif ($r['status']) {
    $status = ucfirst($r['status']);
  }

  // Mobile as text to avoid formatting issues
  $mobile = $r['mobile'] ? "'" . $r['mobile'] : '';

  $row = [
    $r['pass_number'],
    $r['visitor_name'],
    $mobile,
    $r['company'],
    $r['purpose'],
    $r['whom_to_meet'],
    $r['num_of_people'],
    $r['host_name'],
    $apptDate,
    $apptTime,
    $checkinDate,
    $checkinTime,
    $checkoutDate,
    $checkoutTime,
    $timeSpent,
    $status
  ];

  fputcsv($output, $row);
}

fclose($output);
exit;
?>