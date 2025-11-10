<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Fetch all hosts for the dropdown (exclude admin and security)
$hostsResult = $conn->query("SELECT id, username FROM users WHERE role = 'host' ORDER BY username ASC");
$hosts = [];
if ($hostsResult) {
    while ($h = $hostsResult->fetch_assoc()) {
        $hosts[] = $h;
    }
}

// Fetch all departments for the dropdown safely
$departments = [];
if ($conn->query("SHOW TABLES LIKE 'departments'")->num_rows > 0) {
    $departmentsResult = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
    if ($departmentsResult) {
        while ($d = $departmentsResult->fetch_assoc()) {
            $departments[] = $d;
        }
    }
}

// Filters
$where = "WHERE a.deleted = 0"; // ✅ Only show non-deleted appointments
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 25;
$offset = ($page - 1) * $limit;

// Default date range
$from = !empty($_GET['from']) ? $_GET['from'] : date('Y-m-d') . 'T00:00';
$to = !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d') . 'T23:59';

// Convert to MySQL DATETIME format
$from_sql = str_replace('T', ' ', $from) . ':00';
$to_sql = str_replace('T', ' ', $to) . ':59';

// Include appointment_time and checkin_time
if (!empty($from) && !empty($to)) {
    $where .= " AND (
        (a.appointment_time IS NOT NULL AND a.appointment_time BETWEEN '$from_sql' AND '$to_sql')
        OR 
        (p.checkin_time IS NOT NULL AND p.checkin_time BETWEEN '$from_sql' AND '$to_sql')
    )";
}

// Status filter
$status_filter = $_GET['status'] ?? '';
if (!empty($status_filter) && $status_filter != 'all') {
    $status = $conn->real_escape_string($status_filter);
    $where .= " AND (p.status = '$status' OR p.status IS NULL)";
}

// Search filter
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND (
        a.visitor_name LIKE '%$search%' OR 
        a.mobile LIKE '%$search%' OR 
        a.company LIKE '%$search%' OR 
        p.pass_number LIKE '%$search%'
    )";
}

// Host filter
$host_filter = $_GET['host'] ?? 'all';
$currentUserId = $_SESSION['user']['id'];
$currentUserRole = $_SESSION['user']['role'] ?? 'host';

if ($currentUserRole == 'host') {
    // Host sees only their own appointments
    $where .= " AND a.host_id = $currentUserId";
} else {
    // Admin or other roles can filter by host
    if (!empty($host_filter) && $host_filter != 'all') {
        $hostId = (int)$host_filter;
        $where .= " AND a.host_id = $hostId";
    }
}

// Department filter safely
$department_filter = $_GET['department'] ?? 'all';
$department_join = "";
if (!empty($department_filter) && $department_filter != 'all') {
    $deptId = (int)$department_filter;
    $where .= " AND a.department_id = $deptId";
}

// Check if departments table exists for join
$departments_exists = $conn->query("SHOW TABLES LIKE 'departments'")->num_rows > 0;
if ($departments_exists) {
    $department_join = "LEFT JOIN departments d ON a.department_id = d.id";
}

// Fetch data using LEFT JOIN
$query = "SELECT a.*, p.pass_number, p.checkin_time, p.checkout_time, p.status, u.username AS host_username,
     a.Department AS department_name
FROM appointments a
LEFT JOIN passes p ON p.appointment_id = a.id
LEFT JOIN users u ON a.host_id = u.id

$where
ORDER BY a.id DESC
LIMIT $limit OFFSET $offset";

// echo $query;
// exit();
$result = $conn->query($query);
if (!$result) {
    die("Query Error: " . $conn->error);
}

// Pagination
$totalQuery = "SELECT COUNT(*) as total FROM appointments a
LEFT JOIN passes p ON p.appointment_id = a.id
LEFT JOIN users u ON a.host_id = u.id
$department_join
$where";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Reports', 'icon' => 'chart-bar']
];

echo erp_header('Reports', $breadcrumbs);
?>

<div class="erp-card mb-1">
    <div class="erp-card-header">
        <h5 class="erp-card-title"><i class="fas fa-filter me-2"></i> Filters & Export</h5>
    </div>
    <div class="erp-card-body">
        <form method="GET" class="row g-1 mb-1 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date & Time</label>
                <input type="datetime-local" id="from" name="from" class="form-control custom-datetime"
                    value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date & Time</label>
                <input type="datetime-local" id="to" name="to" class="form-control custom-datetime"
                    value="<?= htmlspecialchars($to) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Host</label>
                <select name="host" class="form-select">
                    <option value="all" <?= $host_filter == 'all' ? 'selected' : '' ?>>All Hosts</option>
                    <?php foreach ($hosts as $host): ?>
                        <option value="<?= $host['id'] ?>" <?= $host_filter == $host['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($host['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Department</label>
                <select name="department" class="form-select">
                    <option value="all" <?= $department_filter == 'all' ? 'selected' : '' ?>>All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $department_filter == $dept['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="waiting" <?= $status_filter == 'waiting' ? 'selected' : '' ?>>Waiting</option>
                    <option value="inside" <?= $status_filter == 'inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="out" <?= $status_filter == 'out' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end justify-content-between">
                <?= erp_button('Search Filter', 'primary', 'btn-sm w-75', '', 'type="submit"'); ?>
                <div class="dropdown ms-4 w-35">
                    <button class="btn btn-sm dropdown-toggle export-btn" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false" title="Export Options">
                        <i class="fas fa-download me-1"></i> Print/Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item" href="#" onclick="printTableOnly(); return false;">
                                <i class="fas fa-print me-2"></i>Print
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="export_csv.php?<?= http_build_query($_GET) ?>">
                                <i class="fas fa-file-csv me-2"></i>Export CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="export_excel.php?<?= http_build_query($_GET) ?>">
                                <i class="fas fa-file-excel me-2"></i>Export Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h5 class="erp-card-title"><i class="fas fa-table me-2"></i> Visitor Pass Report</h5>
    </div>
    <div class="erp-card-body">
        <?php if ($result && $result->num_rows > 0): ?>
            <div id="printReport" class="table-responsive fixed-header-table" style="max-height:500px; overflow:auto;">
                <table class="table table-bordered table-striped align-middle text-center" style="min-width:1350px;">
                    <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th>Pass No</th>
                            <th>Visitor</th>
                            <th>Mobile</th>
                            <th>Company</th>
                            <th>Purpose</th>
                            <th>Whom to Meet</th>
                            <th>No. of People</th>
                            <th>Host</th>
                            <?php if ($departments_exists): ?>
                            <th>Department</th>
                            <?php endif; ?>
                            <th style="width:150px;">Appointment Time</th>
                            <th style="width:150px;">Check-in</th>
                            <th style="width:150px;">Check-out</th>
                            <th>Time Spent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['pass_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                                <td><?= htmlspecialchars($row['mobile']) ?></td>
                                <td><?= htmlspecialchars($row['company']) ?></td>
                                <td><?= htmlspecialchars($row['purpose']) ?></td>
                                <td><?= htmlspecialchars($row['whom_to_meet']) ?></td>
                                <td><?= htmlspecialchars($row['num_of_people']) ?></td>
                                <td><?= htmlspecialchars($row['host_username']) ?></td>
                                <?php if ($departments_exists): ?>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <?php endif; ?>
                                <td><?= $row['appointment_time'] ?></td>
                                <td><?= $row['checkin_time'] ?: '-' ?></td>
                                <td><?= $row['checkout_time'] ?: '-' ?></td>
                                <td>
                                    <?php
                                    if ($row['checkin_time'] && $row['checkout_time']) {
                                        $diff = strtotime($row['checkout_time']) - strtotime($row['checkin_time']);
                                        echo gmdate('H:i:s', $diff);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?= ucfirst($row['status'] ?? 'waiting') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-end">
                <span class="small">Showing <?= $offset + 1 ?> - <?= min($offset + $limit, $totalRows) ?> of <?= $totalRows ?> entries</span>
            </div>
        <?php else: ?>
            <div class="text-center text-muted">No records found.</div>
        <?php endif; ?>
    </div>
</div>

<script>
function printTableOnly() {
    const printContents = document.getElementById('printReport').innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>

<?php echo erp_footer(); ?>