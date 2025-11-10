<?php
session_start();
require '../includes/db.php';

// Filename with timestamp
$filename = "visitor_pass_report_" . date("Y-m-d_H-i") . ".csv";

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// --- Filters for report header ---
$from = !empty($_GET['from']) ? $_GET['from'] : date('Y-m-d') . 'T00:00';
$to = !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d') . 'T23:59';
$host_filter = $_GET['host'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$department_filter = $_GET['department'] ?? 'all';

// Host name for header
$host_name_text = "All";
if ($host_filter != 'all') {
    $hres = $conn->query("SELECT username FROM users WHERE id=" . (int)$host_filter);
    if ($hres && $hres->num_rows) {
        $host_name_text = $hres->fetch_assoc()['username'];
    }
}

// Department name for header
$department_name_text = "All";
if ($department_filter != 'all') {
    $dres = $conn->query("SELECT name FROM departments WHERE id=" . (int)$department_filter);
    if ($dres && $dres->num_rows) {
        $department_name_text = $dres->fetch_assoc()['name'];
    }
}

// Company header info
fputcsv($output, ["Company Name: PDVS"]);
fputcsv($output, ["Address: Kolhapur"]);
fputcsv($output, ["Generated On: " . date("d M Y, H:i")]);
fputcsv($output, ["From: $from, To: $to, Host: $host_name_text, Status: $status_filter, Department: $department_name_text"]);
fputcsv($output, []); // blank row

// Column headers
$headers = [
    'Pass No','Visitor Name','Email','Mobile','Company','Purpose','Whom to Meet',
    'No. of People','Host','Department','Appointment Date','Appointment Time',
    'Check-in Date','Check-in Time','Check-out Date','Check-out Time','Time Spent','Status'
];
fputcsv($output, $headers);

// Filters
$where = "WHERE 1=1";

// Convert to MySQL DATETIME format
$from_sql = str_replace('T', ' ', $from) . ':00';
$to_sql = str_replace('T', ' ', $to) . ':59';

// Appointment / Check-in date filter
$where .= " AND (
    (a.appointment_time IS NOT NULL AND a.appointment_time BETWEEN '$from_sql' AND '$to_sql')
    OR 
    (p.checkin_time IS NOT NULL AND p.checkin_time BETWEEN '$from_sql' AND '$to_sql')
)";

// Company filter
if (!empty($_GET['company'])) {
    $company = $conn->real_escape_string($_GET['company']);
    $where .= " AND a.company LIKE '%$company%'";
}

// Host filter by ID
if (!empty($_GET['host']) && $_GET['host'] != 'all') {
    $host_id = (int)$_GET['host'];
    $where .= " AND a.host_id = $host_id";
}

// Status filter
if (!empty($_GET['status']) && $_GET['status'] != 'all') {
    $status = $conn->real_escape_string($_GET['status']);
    if ($status == 'waiting') {
        $where .= " AND (p.status IS NULL OR p.status = 'waiting')";
    } else {
        $where .= " AND p.status = '$status'";
    }
}

// Search filter
if (!empty($_GET['search'])) {
    $s = $conn->real_escape_string($_GET['search']);
    $where .= " AND (a.visitor_name LIKE '%$s%' OR a.mobile LIKE '%$s%' OR a.company LIKE '%$s%' OR a.email LIKE '%$s%' OR p.pass_number LIKE '%$s%')";
}

// Department join
$departments_exists = $conn->query("SHOW TABLES LIKE 'departments'")->num_rows > 0;
$department_join = $departments_exists ? "LEFT JOIN departments d ON a.department_id = d.id" : "";

// Department filter
if (!empty($department_filter) && $department_filter != 'all') {
    $deptId = (int)$department_filter;
    $where .= " AND a.department_id = $deptId";
}

// Fetch data
$query = "SELECT p.*, a.visitor_name, a.mobile, a.company, a.purpose, a.whom_to_meet, a.num_of_people, a.appointment_time, a.email,
          COALESCE(u.username,'') AS host_name, " . ($departments_exists ? "COALESCE(d.name,'') AS department_name" : "'' AS department_name") . "
          FROM passes p
          JOIN appointments a ON p.appointment_id = a.id
          LEFT JOIN users u ON a.host_id = u.id
          $department_join
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
    } else {
        $status = 'Waiting';
    }

    // Mobile as text
    $mobile = $r['mobile'] ? "'" . $r['mobile'] : '';

    $row = [
        $r['pass_number'],
        $r['visitor_name'],
        $r['email'],
        $mobile,
        $r['company'],
        $r['purpose'],
        $r['whom_to_meet'],
        $r['num_of_people'],
        $r['host_name'],
        $r['department_name'],
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
