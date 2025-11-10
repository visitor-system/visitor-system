<?php
session_start();
require '../includes/db.php';

// Filename
$filename = "visitor_pass_report_" . date("Y-m-d_H-i") . ".xls";

// Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Start output
echo "<table border='1'>";
echo "<tr><th colspan='18' style='font-size:16pt;'>Company Name: PDVS</th></tr>";
echo "<tr><th colspan='18'>Address: Kolhapur</th></tr>";

// --- Filters for report header ---
$from = !empty($_GET['from']) ? $_GET['from'] : date('Y-m-d') . 'T00:00';
$to = !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d') . 'T23:59';
$host_filter = $_GET['host'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$department_filter = $_GET['department'] ?? 'all';

// Show host/department names in header
$host_name_text = "All";
if ($host_filter != 'all') {
    $hres = $conn->query("SELECT username FROM users WHERE id=" . (int)$host_filter);
    if ($hres && $hres->num_rows) {
        $host_name_text = $hres->fetch_assoc()['username'];
    }
}

$department_name_text = "All";
if ($department_filter != 'all') {
    $dres = $conn->query("SELECT name FROM departments WHERE id=" . (int)$department_filter);
    if ($dres && $dres->num_rows) {
        $department_name_text = $dres->fetch_assoc()['name'];
    }
}

echo "<tr><th colspan='18'>Generated On: " . date("d M Y, H:i") . "</th></tr>";
echo "<tr><th colspan='18'>From: $from, To: $to, Host: $host_name_text, Status: $status_filter, Department: $department_name_text</th></tr>";
echo "<tr></tr>"; // blank row

// Column headers
$headers = [
  'Pass No','Visitor Name','Email','Mobile','Company','Purpose','Whom to Meet',
  'No. of People','Host','Department','Appointment Date','Appointment Time',
  'Check-in Date','Check-in Time','Check-out Date','Check-out Time','Time Spent','Status'
];
echo "<tr>";
foreach ($headers as $header) {
    echo "<th>$header</th>";
}
echo "</tr>";

// Filters
$where = "WHERE 1=1";

// Convert to MySQL DATETIME format
$from_sql = str_replace('T', ' ', $from) . ':00';
$to_sql = str_replace('T', ' ', $to) . ':59';

// Appointment / Checkin date filter
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
    } else {
        $status = 'Waiting';
    }

    echo "<tr>";
    echo "<td>{$r['pass_number']}</td>";
    echo "<td>{$r['visitor_name']}</td>";
    echo "<td>{$r['email']}</td>";
    echo "<td>'{$r['mobile']}</td>";
    echo "<td>{$r['company']}</td>";
    echo "<td>{$r['purpose']}</td>";
    echo "<td>{$r['whom_to_meet']}</td>";
    echo "<td>{$r['num_of_people']}</td>";
    echo "<td>{$r['host_name']}</td>";
    echo "<td>{$r['department_name']}</td>";
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