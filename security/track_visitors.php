<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get visitor statistics with error handling
$total_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes");
$total_visitors = ($total_visitors_result && $total_visitors_result !== false) ? $total_visitors_result->fetch_assoc()['total'] : 0;

$inside_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes WHERE status = 'inside'");
$inside_visitors = ($inside_visitors_result && $inside_visitors_result !== false) ? $inside_visitors_result->fetch_assoc()['total'] : 0;

$waiting_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes WHERE status = 'waiting'");
$waiting_visitors = ($waiting_visitors_result && $waiting_visitors_result !== false) ? $waiting_visitors_result->fetch_assoc()['total'] : 0;

$checked_out_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes WHERE status = 'out'");
$checked_out_visitors = ($checked_out_visitors_result && $checked_out_visitors_result !== false) ? $checked_out_visitors_result->fetch_assoc()['total'] : 0;

$today_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes p JOIN appointments a ON p.appointment_id = a.id WHERE DATE(a.appointment_time) = CURDATE()");
$today_visitors = ($today_visitors_result && $today_visitors_result !== false) ? $today_visitors_result->fetch_assoc()['total'] : 0;

$this_week_visitors_result = $conn->query("SELECT COUNT(*) as total FROM passes p JOIN appointments a ON p.appointment_id = a.id WHERE WEEK(a.appointment_time) = WEEK(NOW())");
$this_week_visitors = ($this_week_visitors_result && $this_week_visitors_result !== false) ? $this_week_visitors_result->fetch_assoc()['total'] : 0;



// Search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// EXACT COPY FROM DASHBOARD - WORKING CODE
$search_sql = "";
$host_only_clause = '';

// Search by name, mobile, pass number
if ($search) {
    $clean_search = $conn->real_escape_string($search);
    $is_digits = ctype_digit($clean_search);
    if ($is_digits) {
        if (strlen($clean_search) === 10) {
            $search_sql .= " AND appointments.mobile = '$clean_search'";
        } else {
            $invalid_mobile = true;
        }
    } else {
        $search_sql .= " AND (appointments.visitor_name LIKE '%$clean_search%' OR passes.pass_number LIKE '%$clean_search%')";
    }
}

// Date filtering
if ($from_date && $to_date) {
    $search_sql .= " AND DATE(appointments.appointment_time) BETWEEN '$from_date' AND '$to_date'";
}

// Status filtering
if ($status_filter) {
    $search_sql .= " AND passes.status = '$status_filter'";
}

$query = "SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.appointment_time 
          FROM passes 
          JOIN appointments ON passes.appointment_id = appointments.id 
          WHERE 1=1 $host_only_clause $search_sql 
          ORDER BY passes.id DESC";

$result = $conn->query($query);

// Debug: Check if query worked
if (!$result) {
    echo "<!-- Query failed: " . $conn->error . " -->";
    echo "<!-- Query was: " . $query . " -->";
}

// Get status distribution data
$status_distribution_result = $conn->query("SELECT status, COUNT(*) as count FROM passes GROUP BY status");
if ($status_distribution_result && $status_distribution_result !== false) {
    $status_distribution = $status_distribution_result->fetch_all(MYSQLI_ASSOC);
} else {
    $status_distribution = [];
}

// Get hourly visitor patterns (last 24 hours)
$hourly_patterns = [];
for ($i = 23; $i >= 0; $i--) {
    $hour = date('H:00', strtotime("-$i hours"));
    $count_query = $conn->query("SELECT COUNT(*) as total FROM passes p JOIN appointments a ON p.appointment_id = a.id WHERE HOUR(a.appointment_time) = HOUR(NOW() - INTERVAL $i HOUR)");
    if ($count_query && $count_query !== false) {
        $count = $count_query->fetch_assoc()['total'];
    } else {
        $count = 0;
    }
    $hourly_patterns[] = ['hour' => $hour, 'count' => $count];
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => '', 'icon' => 'user-check']
];

echo erp_header('Visitor Tracking Dashboard', $breadcrumbs);
?>

<!-- ERP Stats Cards -->
<div class="erp-stats-grid">
    <?php echo erp_stat_card('fas fa-users', $total_visitors, 'Total Visitors', null, 'primary'); ?>
    <?php echo erp_stat_card('fas fa-user-check', $inside_visitors, 'Currently Inside', null, 'success'); ?>
    <?php echo erp_stat_card('fas fa-clock', $waiting_visitors, 'Waiting Visitors', null, 'warning'); ?>
    <?php echo erp_stat_card('fas fa-sign-out-alt', $checked_out_visitors, 'Checked Out', null, 'info'); ?>
    <?php echo erp_stat_card('fas fa-calendar-day', $today_visitors, 'Today\'s Visitors', null, 'primary'); ?>
    <?php echo erp_stat_card('fas fa-calendar-week', $this_week_visitors, 'This Week', null, 'success'); ?>
</div>
<!-- Visitor Management -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-users"></i>
            Visitor Management
        </h3>
        <div>
            <button onclick="printTableOnly()" class="erp-btn erp-btn-secondary erp-btn-sm">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Search & Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="erp-form-label">Search</label>
            <input type="text" name="search" class="erp-form-control"
                placeholder="Search by name, company, mobile, pass number" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <label class="erp-form-label">From Date</label>
            <input type="date" name="from_date" class="erp-form-control" value="<?= htmlspecialchars($from_date) ?>">
        </div>
        <div class="col-md-2">
            <label class="erp-form-label">To Date</label>
            <input type="date" name="to_date" class="erp-form-control" value="<?= htmlspecialchars($to_date) ?>">
        </div>
        <div class="col-md-2">
            <label class="erp-form-label">Status</label>
            <select name="status" class="erp-form-control">
                <option value="">All Status</option>
                <option value="waiting" <?= $status_filter == 'waiting' ? 'selected' : '' ?>>Waiting</option>
                <option value="inside" <?= $status_filter == 'inside' ? 'selected' : '' ?>>Inside</option>
                <option value="out" <?= $status_filter == 'out' ? 'selected' : '' ?>>Checked Out</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="erp-btn erp-btn-primary w-100">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <a href="track_visitors.php" class="erp-btn erp-btn-secondary w-100">
                <i class="fas fa-refresh"></i>
            </a>
        </div>
    </form>

    <!-- Visitor Table with Scroll -->
    <div class="erp-table-container">
        <table class="erp-table" data-datatable>
            <thead>
                <tr>
                    <th>Pass No</th>
                    <th>Visitor Name</th>
                    <th>Company</th>
                    <th>Purpose</th>
                    <th>Host Name</th>
                    <th>Book Time</th>
                    <th>Status</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Time Spent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $status = $row['status'];
                        if ($status == 'waiting') {
                            $badgeType = 'warning';
                            $statusLabel = 'Pending Visit';
                        } elseif ($status == 'inside') {
                            $badgeType = 'success';
                            $statusLabel = 'Checked In';
                        } elseif ($status == 'out') {
                            $badgeType = 'info';
                            $statusLabel = 'Checked Out';
                        } else {
                            $badgeType = 'danger';
                            $statusLabel = 'check Out';
                        }

                        $checkinDisplay = !empty($row['checkin_time']) ? date("d M Y, h:i A", strtotime($row['checkin_time'])) : '—';
                        $checkoutDisplay = !empty($row['checkout_time']) ? date("d M Y, h:i A", strtotime($row['checkout_time'])) : '—';

                        if (!empty($row['checkin_time']) && !empty($row['checkout_time'])) {
                            $diff = strtotime($row['checkout_time']) - strtotime($row['checkin_time']);
                            $hours = floor($diff / 3600);
                            $minutes = floor(($diff % 3600) / 60);
                            $timeSpent = "{$hours}h {$minutes}m";
                        } else {
                            $timeSpent = '—';
                        }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['pass_number']) ?></strong></td>
                            <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                            <td><?= htmlspecialchars($row['company']) ?></td>
                            <td><?= htmlspecialchars($row['purpose'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['host_name'] ?? 'N/A') ?></td>
                            <td><?= date("d M Y, h:i A", strtotime($row['appointment_time'])) ?></td>
                            <td><?php echo erp_badge($statusLabel, $badgeType); ?></td>
                            <td><?= $checkinDisplay ?></td>
                            <td><?= $checkoutDisplay ?></td>
                            <td><?= $timeSpent ?></td>
                            <td>
                                <?php if ($status == 'waiting'): ?>
                                    <a href="checkin.php?id=<?= $row['id'] ?>" class="erp-btn erp-btn-success erp-btn-sm">
                                        <i class="fas fa-sign-in-alt"></i> In
                                    </a>
                                <?php elseif ($status == 'inside'): ?>
                                    <a href="javascript:void(0)" class="erp-btn erp-btn-warning erp-btn-sm"
                                        onclick="erpConfirm('Check Out Visitor', 'Are you sure you want to check out this visitor?', 'Yes, check out!', 'Cancel').then((result) => { if (result.isConfirmed) { window.location.href = 'checkout.php?id=<?= $row['id'] ?>'; } })">
                                        <i class="fas fa-sign-out-alt"></i> Out
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <?php if ($result === false): ?>
                                <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i><br>
                                <span class="text-danger">Error loading visitor data. Please try again.</span>
                            <?php else: ?>
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                No visitors found
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- CSS for Scrollable Table with Sticky Headers -->
<style>
    .erp-table-container {
        max-height: 500px;
        /* Set table container height */
        overflow-y: auto;
        /* Vertical scrollbar */
        overflow-x: auto;
        /* Horizontal scrollbar if table is wide */
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .erp-table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .erp-table-container thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        /* Header background */
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }
</style>