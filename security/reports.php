<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Filters with default current date and time
$where = "WHERE 1=1";
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 25;
$offset = ($page - 1) * $limit;

// Default From/To dates with 09:00 to 18:00
$from = !empty($_GET['from']) ? $_GET['from'] : date('Y-m-d') . 'T09:00';
$to = !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d') . 'T18:00';

if (!empty($from) && !empty($to)) {
    $where .= " AND CAST(a.appointment_time AS DATETIME) BETWEEN '$from' AND '$to'";
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

// Fetch passes with appointments data
$query = "SELECT 
    p.*, 
    a.visitor_name, 
    a.mobile, 
    a.company, 
    a.purpose, 
    a.whom_to_meet,
    a.num_of_people,
    a.appointment_time, 
    u.username AS host_name 
FROM passes p
JOIN appointments a ON p.appointment_id = a.id
JOIN users u ON a.host_id = u.id
$where
ORDER BY p.id DESC
LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Pagination
$totalQuery = "SELECT COUNT(*) as total FROM passes p
  JOIN appointments a ON p.appointment_id = a.id
  JOIN users u ON a.host_id = u.id $where";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Analytics
$today = date('Y-m-d');
$todayCount = $conn->query("SELECT COUNT(*) as c FROM passes WHERE DATE(checkin_time) = '$today'")->fetch_assoc()['c'];
$activeCount = $conn->query("SELECT COUNT(*) as c FROM passes WHERE status = 'inside'")->fetch_assoc()['c'];
$expiredCount = $conn->query("SELECT COUNT(*) as c FROM passes WHERE status = 'out'")->fetch_assoc()['c'];

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Reports', 'icon' => 'chart-bar']
];

echo erp_header(' Reports', $breadcrumbs);
?>

<!DOCTYPE html>
<html lang="en">



<!-- Filters and Export -->
<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h5 class="erp-card-title">
            <i class="fas fa-filter me-2"></i>
            Filters & Export
        </h5>
    </div>
    <div class="erp-card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label">From Date & Time</label>
                <input type="datetime-local" id="from" name="from" class="form-control"
                    value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date & Time</label>
                <input type="datetime-local" id="to" name="to" class="form-control"
                    value="<?= htmlspecialchars($to) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Company</label>
                <input type="text" name="company" class="form-control" placeholder="Company name"
                    value="<?= htmlspecialchars($_GET['company'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Host</label>
                <input type="text" name="host" class="form-control" placeholder="Host name"
                    value="<?= htmlspecialchars($_GET['host'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="inside" <?= ($_GET['status'] ?? '') === 'inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="out" <?= ($_GET['status'] ?? '') === 'out' ? 'selected' : '' ?>>Out</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <?= erp_button('Search', 'primary', 'btn-sm', '', 'type="submit"'); ?>
            </div>
        </form>

        <div class="text-end mb-3">
            <button onclick="printTableOnly()" class="btn btn-secondary btn-sm me-2">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="export_csv.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm me-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<!-- Reports Table -->
<div class="erp-card">
    <div class="erp-card-header">
        <h5 class="erp-card-title">
            <i class="fas fa-table me-2"></i>
            Visitor Pass Report
        </h5>
    </div>
    <div class="erp-card-body">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive" style="max-height:500px; overflow:auto;">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Pass No</th>
                            <th>Visitor</th>
                            <th>Mobile</th>
                            <th>Company</th>
                            <th>Purpose</th>
                            <th>Whom to Meet</th>
                            <th>No. of People</th>
                            <th>Host</th>
                            <th>Appointment Time</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Time Spent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $checkin = $row['checkin_time'] ? date("d M Y, h:i A", strtotime($row['checkin_time'])) : '—';
                            $checkout = $row['checkout_time'] ? date("d M Y, h:i A", strtotime($row['checkout_time'])) : '—';
                            $appointment = $row['appointment_time'] ? date("d M Y, h:i A", strtotime($row['appointment_time'])) : '—';
                            $timeSpent = ($row['checkin_time'] && $row['checkout_time']) ? floor((strtotime($row['checkout_time']) - strtotime($row['checkin_time'])) / 60) . " mins" : '—';
                            $statusLabel = ($row['checkout_time']) ? 'Completed' : ucfirst($row['status']);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['pass_number'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($row['visitor_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['mobile'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['company'] ?? '—') ?></td>
                                <td><?= htmlspecialchars(substr($row['purpose'] ?? '—', 0, 50)) ?><?= strlen($row['purpose'] ?? '') > 50 ? '...' : '' ?>
                                </td>
                                <td><?= htmlspecialchars($row['whom_to_meet'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['num_of_people'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['host_name'] ?? '—') ?></td>
                                <td><?= $appointment ?></td>
                                <td><?= $checkin ?></td>
                                <td><?= $checkout ?></td>
                                <td><?= $timeSpent ?></td>
                                <td><?= $statusLabel ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <?= erp_alert('No visitor records found matching your criteria.', 'info'); ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-close datetime pickers
        ['from', 'to'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('input', () => el.blur());
        });

        window.printTableOnly = function () {
            const table = document.querySelector('.table-responsive table');
            if (!table) { alert('No table found to print'); return; }
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
        <!DOCTYPE html><html><head><title>Visitor Report</title>
        <style>
            body{font-family:Arial,sans-serif;margin:20px;}
            table{width:100%;border-collapse:collapse;margin-top:20px;}
            th,td{border:1px solid #ddd;padding:8px;text-align:left;}
            th{background:#f2f2f2;font-weight:bold;}
            @media print{body{margin:0;} table{font-size:12px;}}
        </style></head><body>
            <h2>Visitor Pass Report</h2>
            <p><strong>Generated on:</strong> ${new Date().toLocaleString()}</p>
            ${table.outerHTML}
        </body></html>`);
            printWindow.document.close();
            printWindow.print();
        };
    });
</script>

<?php echo erp_footer(); ?>