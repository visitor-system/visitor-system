<?php
session_start();
require '../includes/db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=visitor_pass_report.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Filters
$where = "WHERE 1=1";

if (!empty($_GET['from']) && !empty($_GET['to'])) {
  $from = $_GET['from'];
  $to = $_GET['to'];
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
  $search = $conn->real_escape_string($_GET['search']);
  $where .= " AND (
    a.visitor_name LIKE '%$search%' OR 
    a.mobile LIKE '%$search%' OR 
    a.company LIKE '%$search%' OR 
    p.pass_number LIKE '%$search%'
  )";
}

$query = "SELECT p.*, a.visitor_name, a.mobile, a.company, a.purpose, a.appointment_time, u.username AS host_name 
          FROM passes p
          JOIN appointments a ON p.appointment_id = a.id
          JOIN users u ON a.host_id = u.id
          $where
          ORDER BY p.id DESC";

$result = $conn->query($query);


// Static header info
$companyName = "PDVS";
$address = "Kolhapur";
$currentDateTime = date("d M Y, h:i A");

echo "<table border='1'>";
echo "<tr><td colspan='9'><strong>Company Name:</strong> $companyName</td></tr>";
echo "<tr><td colspan='9'><strong>Address:</strong> $address</td></tr>";
echo "<tr><td colspan='9'><strong>Date-Time:</strong> $currentDateTime</td></tr>";
echo "<tr><td colspan='9'></td></tr>"; // Spacer row

//Output table
echo "<tr>
  <th>Pass No</th>
  <th>Visitor Name</th>
  <th>Contact</th>
  <th>Company</th>
  <th>Purpose</th>
  <th>Host</th>
  <th>Check-in</th>
  <th>Check-out</th>
  <th>Time Spent</th>
</tr>";

while ($row = $result->fetch_assoc()) {
  $checkin = $row['checkin_time'] ? date("d M Y, h:i A", strtotime($row['checkin_time'])) : '—';
  $checkout = $row['checkout_time'] ? date("d M Y, h:i A", strtotime($row['checkout_time'])) : '—';
  $timeSpent = ($row['checkin_time'] && $row['checkout_time']) ?
    floor((strtotime($row['checkout_time']) - strtotime($row['checkin_time'])) / 60) . " mins" : '—';

  echo "<tr>
    <td>{$row['pass_number']}</td>
    <td>{$row['visitor_name']}</td>
    <td>{$row['mobile']}</td>
    <td>{$row['company']}</td>
    <td>{$row['purpose']}</td>
    <td>{$row['host_name']}</td>
    <td>{$checkin}</td>
    <td>{$checkout}</td>
    <td>{$timeSpent}</td>
  </tr>";
}
echo "</table>";
?>