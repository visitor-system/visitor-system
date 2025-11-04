<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Helper: Format date/time in one line
function formatDateTime($datetime)
{
    return (!empty($datetime) && $datetime != '0000-00-00 00:00:00')
        ? date('d M Y, h:i A', strtotime($datetime))
        : '—';
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
    $status_safe = $conn->real_escape_string(strtolower($status_filter));
    $search_sql .= " AND LOWER(passes.status)='$status_safe'";
}

$query = "SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.purpose, users.username AS host_name, appointments.appointment_time, appointments.status
          FROM passes
          JOIN appointments ON passes.appointment_id=appointments.id
          JOIN users ON appointments.host_id=users.id
          WHERE $search_sql
          ORDER BY passes.id DESC";

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $res = $conn->query($query);
    $rows = [];
    $serial = 1;
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = [
                'sr_no' => $serial++,
                'id' => (int) $r['id'],
                'pass_number' => $r['pass_number'],
                'visitor_name' => $r['visitor_name'],
                'company' => $r['company'],
                'purpose' => $r['purpose'],
                'host_name' => !empty($r['host_name']) ? $r['host_name'] : '—',
                'appointment_time' => formatDateTime($r['appointment_time']),
                'status' => strtolower($r['status']),
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

$result = $conn->query($query);

$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Visitor Tracking', 'url' => '', 'icon' => 'user-check']
];

echo erp_header('Visitor Tracking Dashboard', $breadcrumbs);
?>

<script src="../includes/js/qrcode.min.js"></script>
<style>
    /* Filter section static */
    .erp-filter-body {
        padding: 10px;
    }

    /* Table scroll container */
    .erp-card-body-table {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        padding: 0;
    }

    /* ERP table */
    .erp-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        font-size: 13px;
    }

    /* Table header */
    .erp-table thead th {
        background: #004080;
        color: #fff;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
    }

    /* Table cells */
    .erp-table td {
        padding: 8px;
        border: 1px solid #ddd;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        height: 42px;
    }

    /* Center QR and Pass No */
    .erp-table td>div[id^="passQR"],
    .erp-table td>span[id^="passNo"] {
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>

<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-filter"></i> Filter Visitors</h3>
    </div>
    <div class="erp-filter-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <input type="text" name="search" class="erp-form-control" placeholder="Search name"
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
                <button type="submit" class="erp-btn erp-btn-primary"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title"><i class="fas fa-users"></i> Visitor Management</h3>
    </div>
    <div class="erp-card-body-table">
        <table class="erp-table" id="visitorTable">
            <thead>
                <tr>
                    <th>Sr No</th>
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
                <?php
                $serial = 1;
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= (int) $row['id'] ?>">
                            <td><?= $serial++ ?></td>
                            <td>
                                <div id="passQR<?= $row['id'] ?>"></div>
                                <span id="passNo<?= $row['id'] ?>"><?= htmlspecialchars($row['pass_number']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                            <td><?= htmlspecialchars($row['company']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td><?= htmlspecialchars($row['host_name'] ?: '—') ?></td>
                            <td><?= formatDateTime($row['appointment_time']) ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td><?= formatDateTime($row['checkin_time']) ?></td>
                            <td><?= formatDateTime($row['checkout_time']) ?></td>
                            <td><?= htmlspecialchars($row['time_spent'] ?? '—') ?></td>
                            <td>
                                <?php if ($row['status'] == 'waiting'): ?>
                                    <a href="checkin.php?id=<?= $row['id'] ?>" class="erp-btn erp-btn-success">In</a>
                                <?php elseif ($row['status'] == 'inside'): ?>
                                    <a href="checkout.php?id=<?= $row['id'] ?>" class="erp-btn erp-btn-warning">Out</a>
                                <?php else: ?>
                                    Completed
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="12" class="text-center">No visitors found</td>
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
            return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function statusLabel(s) {
            if (s === 'waiting') return 'Waiting';
            if (s === 'inside') return 'Inside';
            if (s === 'out' || s === 'completed') return 'Completed';
            return '—';
        }

        function renderRows(rows) {
            const tbody = document.getElementById('visitorTbody');
            if (!tbody) return;
            if (!rows || rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" class="text-center">No visitors found</td></tr>';
                return;
            }

            let html = '';
            rows.forEach(r => {
                let actionHtml = '';
                if (r.status === 'waiting') actionHtml = `<a href="checkin.php?id=${r.id}" class="erp-btn erp-btn-success">In</a>`;
                else if (r.status === 'inside') actionHtml = `<a href="checkout.php?id=${r.id}" class="erp-btn erp-btn-warning">Out</a>`;
                else actionHtml = 'Completed';

                html += `<tr data-id="${r.id}">
                <td>${r.sr_no}</td>
                <td>
                    <div id="passQR${r.id}" style="margin:auto;"></div>
                    <span id="passNo${r.id}" style="display:flex;justify-content:center;">${escapeHtml(r.pass_number)}</span>
                </td>
                <td id="name${r.id}">${escapeHtml(r.visitor_name)}</td>
                <td id="com${r.id}">${escapeHtml(r.company)}</td>
                <td id="purpose${r.id}">${escapeHtml(r.purpose)}</td>
                <td id="hname${r.id}">${escapeHtml(r.host_name || '—')}</td>
                <td id="appointment_time${r.id}">${escapeHtml(r.appointment_time)}</td>
                <td id="status${r.id}">${escapeHtml(statusLabel(r.status))}</td>
                <td id="checkin${r.id}">${escapeHtml(r.checkin_time)}</td>
                <td id="checkout${r.id}">${escapeHtml(r.checkout_time)}</td>
                <td id="time_spend${r.id}">${escapeHtml(r.time_spent)}</td>
                <td>${actionHtml}</td>
            </tr>`;
            });
            tbody.innerHTML = html;

            document.querySelectorAll('[id^="passQR"]').forEach(el => {
                const rowId = el.id.match(/\d+/g);
                // const MCode = document.getElementById("passNo" + rowId).innerText.trim();
                // MCode = `visitor Name: ${document.getElementById("name" + rowId).innerText.trim()}
                // Company: ${document.getElementById("com" + rowId).innerText.trim()}
                // Whom to Meet: ${document.getElementById("whom" + rowId).innerText.trim()}
                // Host Name: ${document.getElementById("hname" + rowId).innerText.trim()}
                // Purpose: ${document.getElementById("purpose" + rowId).innerText.trim()}
                // Appointment Date & Time: ${document.getElementById("appointment_time" + rowId).innerText.trim()}
                // Number of People: ${document.getElementById("num_of_people" + rowId).innerText.trim()}
                // Pass Number: ${document.getElementById("passNo" + rowId).innerText.trim()}
                // Status: Waiting`;

                MCode = `visitor Name: ${document.getElementById("name" + rowId).innerText.trim()}
                Company: ${document.getElementById("com" + rowId).innerText.trim()}
                Host Name: ${document.getElementById("hname" + rowId).innerText.trim()}
                Purpose: ${document.getElementById("purpose" + rowId).innerText.trim()}
                Appointment Date & Time: ${document.getElementById("appointment_time" + rowId).innerText.trim()}
                Pass Number: ${document.getElementById("passNo" + rowId).innerText.trim()}
                Status: test`;

                el.innerHTML = "";
                if (MCode !== "") {
                    new QRCode(el, {
                        text: MCode,
                        width: 90,
                        height: 90,
                        correctLevel: QRCode.CorrectLevel.L
                    });
                }
            });
        }

        async function fetchAndUpdate() {
            try {
                const res = await fetch(getAjaxUrl(), { credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
                renderRows(data);
            } catch (e) { console.error('Visitor table refresh failed', e); }
        }

        fetchAndUpdate();
        setInterval(fetchAndUpdate, POLL_INTERVAL_MS);
    })();
</script>