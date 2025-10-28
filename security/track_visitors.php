<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Helpers
function formatDateTime($datetime)
{
    return (!empty($datetime) && $datetime != '0000-00-00 00:00:00') ? date('d M Y, h:i A', strtotime($datetime)) : '—';
}

// Read filters
$search = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? '';

$search_sql = "1=1";

if ($search) {
    $clean_search = $conn->real_escape_string($search);
    if (ctype_digit($clean_search) && strlen($clean_search) === 10) {
        $search_sql .= " AND appointments.mobile='$clean_search'";
    } else {
        $search_sql .= " AND (appointments.visitor_name LIKE '%$clean_search%' OR passes.pass_number LIKE '%$clean_search%')";
    }
}

if ($from_date && $to_date) {
    $from_date_safe = $conn->real_escape_string($from_date);
    $to_date_safe = $conn->real_escape_string($to_date);
    $search_sql .= " AND DATE(appointments.appointment_time) BETWEEN '$from_date_safe' AND '$to_date_safe'";
}

if ($status_filter) {
    $status_safe = $conn->real_escape_string($status_filter);
    $search_sql .= " AND passes.status='$status_safe'";
}

$query = "SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.purpose, 
                 appointments.host_name, appointments.appointment_time
          FROM passes
          JOIN appointments ON passes.appointment_id=appointments.id
          WHERE $search_sql
          ORDER BY passes.id DESC";

// AJAX endpoint
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $res = $conn->query($query);
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'id' => (int) $r['id'],
                'pass_number' => $r['pass_number'],
                'visitor_name' => $r['visitor_name'],
                'company' => $r['company'],
                'purpose' => $r['purpose'],
                'host_name' => $r['host_name'],
                'appointment_time' => formatDateTime($r['appointment_time']),
                'status' => $r['status'],
                'checkin_time' => formatDateTime($r['checkin_time']),
                'checkout_time' => formatDateTime($r['checkout_time']),
                'time_spent' => $r['time_spent'] ?? '—'
            ];
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

// Non-AJAX
$result = $conn->query($query);

$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => '', 'icon' => 'user-check']
];

echo erp_header('Visitor Tracking Dashboard', $breadcrumbs);
?>

<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-filter"></i> Filter Visitors</h3>
    </div>
    <div class="erp-card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <input type="text" name="search" class="erp-form-control" placeholder="Search by name or mobile"
                    value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="erp-form-control"
                    value="<?= htmlspecialchars($from_date) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="erp-form-control" value="<?= htmlspecialchars($to_date) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="erp-form-control">
                    <option value="">All Status</option>
                    <option value="waiting" <?= $status_filter == 'waiting' ? 'selected' : '' ?>>Waiting</option>
                    <option value="inside" <?= $status_filter == 'inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="out" <?= $status_filter == 'out' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="erp-btn erp-btn-primary"><i class="fas fa-search"></i> Apply
                    Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-users"></i> Visitor Management</h3>
    </div>
    <div class="erp-card-body">
        <table class="erp-table" id="visitorTable">
            <thead>
                <tr>
                    <th>Pass No</th>
                    <th>Visitor Name</th>
                    <th>Company</th>
                    <th>Purpose</th>
                    <th>Host</th>
                    <th>Appointment</th>
                    <th>Status</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Time Spent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="visitorTbody">
                <?php if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= (int) $row['id'] ?>">
                            <td><?= htmlspecialchars($row['pass_number']) ?></td>
                            <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                            <td><?= htmlspecialchars($row['company']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td><?= htmlspecialchars($row['host_name']) ?></td>
                            <td><?= formatDateTime($row['appointment_time']) ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td class="checkin_col"><?= formatDateTime($row['checkin_time']) ?></td>
                            <td class="checkout_col"><?= formatDateTime($row['checkout_time']) ?></td>
                            <td class="timespent_col"><?= htmlspecialchars($row['time_spent'] ?? '—') ?></td>
                            <td class="action_col">
                                <?php if ($row['status'] == 'waiting'): ?>
                                    <a href="checkin.php?id=<?= $row['id'] ?>" class="erp-btn erp-btn-success action-in">In</a>
                                <?php elseif ($row['status'] == 'inside'): ?>
                                    <a href="checkout.php?id=<?= $row['id'] ?>" class="erp-btn erp-btn-warning action-out">Out</a>
                                <?php else: ?>
                                    Completed
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="11" class="text-center">No visitors found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo erp_footer(); ?>

<script>
    (function () {
        const POLL_INTERVAL_MS = 5000;

        function getAjaxUrl() {
            const url = new URL(window.location.href);
            url.searchParams.set('ajax', '1');
            return url.toString();
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function statusLabel(s) {
            if (s === 'waiting') return 'Waiting';
            if (s === 'inside') return 'Inside';
            if (s === 'out') return 'Completed';
            return 'Completed';
        }

        function renderRows(rows) {
            const tbody = document.getElementById('visitorTbody');
            if (!tbody) return;
            if (!rows || rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center">No visitors found</td></tr>';
                return;
            }

            let html = '';
            rows.forEach(r => {
                let actionHtml = '';
                if (r.status === 'waiting') actionHtml = `<a href="checkin.php?id=${r.id}" class="erp-btn erp-btn-success action-in">In</a>`;
                else if (r.status === 'inside') actionHtml = `<a href="checkout.php?id=${r.id}" class="erp-btn erp-btn-warning action-out">Out</a>`;
                else actionHtml = 'Completed';

                html += `<tr data-id="${r.id}">
                <td>${escapeHtml(r.pass_number)}</td>
                <td>${escapeHtml(r.visitor_name)}</td>
                <td>${escapeHtml(r.company)}</td>
                <td>${escapeHtml(r.purpose)}</td>
                <td>${escapeHtml(r.host_name)}</td>
                <td>${escapeHtml(r.appointment_time)}</td>
                <td>${escapeHtml(statusLabel(r.status))}</td>
                <td class="checkin_col">${escapeHtml(r.checkin_time)}</td>
                <td class="checkout_col">${escapeHtml(r.checkout_time)}</td>
                <td class="timespent_col">${escapeHtml(r.time_spent)}</td>
                <td class="action_col">${actionHtml}</td>
            </tr>`;
            });
            tbody.innerHTML = html;
        }

        async function fetchAndUpdate() {
            try {
                const res = await fetch(getAjaxUrl(), { credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
                renderRows(data);
            } catch (e) {
                console.error('Visitor table refresh failed', e);
            }
        }

        fetchAndUpdate();
        setInterval(fetchAndUpdate, POLL_INTERVAL_MS);

        document.addEventListener('click', function (e) {
            const anchor = e.target.closest('a.action-in, a.action-out');
            if (anchor) anchor.setAttribute('target', '_self');
        });

        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function () {
                fetchAndUpdate();
            });
        }
    })();
</script>