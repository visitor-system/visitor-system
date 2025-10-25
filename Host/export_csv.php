<?php
session_start();
require '../includes/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=visitor_pass_report.csv');

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

// Output CSV
$output = fopen('php://output', 'w');
// Static header info
$companyName = "PDVS";
$address = "Kolhapur";
$currentDateTime = date("d M Y, h:i A");

fputcsv($output, ["Company Name: $companyName"]);
fputcsv($output, ["Address: $address"]);
fputcsv($output, ["Date-Time: $currentDateTime"]);
fputcsv($output, []); // Empty row for spacing

fputcsv($output, ['Pass No', 'Visitor Name', 'Contact', 'Company', 'Purpose', 'Host', 'Check-in', 'Check-out', 'Time Spent']);

while ($row = $result->fetch_assoc()) {
    $checkin = $row['checkin_time'] ? date("d M Y, h:i A", strtotime($row['checkin_time'])) : '—';
    $checkout = $row['checkout_time'] ? date("d M Y, h:i A", strtotime($row['checkout_time'])) : '—';
    $timeSpent = ($row['checkin_time'] && $row['checkout_time']) ?
        floor((strtotime($row['checkout_time']) - strtotime($row['checkin_time'])) / 60) . " mins" : '—';

    fputcsv($output, [
        $row['pass_number'],
        $row['visitor_name'],
        $row['mobile'],
        $row['company'],
        $row['purpose'],
        $row['host_name'],
        $checkin,
        $checkout,
        $timeSpent
    ]);
}
fclose($output);
exit;
?>