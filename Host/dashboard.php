<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../pages/login.php");
  exit;
}

// Host-only filter
$host_only_clause = '';
if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'host') {
  $host_id = intval($_SESSION['user']['id']);
  $host_only_clause = " AND appointments.host_id = $host_id";
}

// Default From/To dates with 09:00 to 18:00
$from_input = $_GET['from'] ?? date('Y-m-d') . 'T09:00';
$to_input = $_GET['to'] ?? date('Y-m-d') . 'T18:00';

function convertToSQLDateTime($dt)
{
  if (!$dt)
    return null;
  return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $dt)));
}

$from_sql = convertToSQLDateTime($from_input);
$to_sql = convertToSQLDateTime($to_input);

// Search filter
$search_sql = '';
$search = $_GET['search'] ?? '';
if ($search) {
  $clean_search = $conn->real_escape_string($search);
  $search_sql .= " AND (appointments.visitor_name LIKE '%$clean_search%' 
                        OR passes.pass_number LIKE '%$clean_search%' 
                        OR appointments.mobile LIKE '%$clean_search%')";
}

// Status filter
$status_filter = $_GET['status'] ?? '';
$status_sql = '';
if ($status_filter) {
  $status_sql = " AND passes.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Date filter
$date_sql = " AND appointments.appointment_time BETWEEN '$from_sql' AND '$to_sql'";

// Fetch counts for cards
$inside = $conn->query("SELECT COUNT(*) as c FROM passes 
                        JOIN appointments ON passes.appointment_id=appointments.id 
                        WHERE passes.status='inside' $host_only_clause $date_sql")->fetch_assoc()['c'];

$waiting = $conn->query("SELECT COUNT(*) as c FROM passes 
                         JOIN appointments ON passes.appointment_id=appointments.id 
                         WHERE passes.status='waiting' $host_only_clause $date_sql")->fetch_assoc()['c'];

$checked_out = $conn->query("SELECT COUNT(*) as c FROM passes 
                             JOIN appointments ON passes.appointment_id=appointments.id 
                             WHERE passes.checkin_time IS NOT NULL 
                               AND passes.checkout_time IS NOT NULL
                               $host_only_clause $date_sql")->fetch_assoc()['c'];

// Fetch records for popup
$all_records = [];
$records_result = $conn->query("SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.appointment_time, passes.status 
                                FROM passes 
                                JOIN appointments ON passes.appointment_id=appointments.id
                                WHERE 1 $host_only_clause $date_sql
                                ORDER BY passes.id DESC");
while ($row = $records_result->fetch_assoc()) {
  $all_records[] = $row;
}

// Chart data
$chart_query = "SELECT DATE(appointments.appointment_time) as appt_date, 
                       SUM(CASE WHEN passes.status='waiting' THEN 1 ELSE 0 END) as waiting_count,
                       SUM(CASE WHEN passes.status='inside' THEN 1 ELSE 0 END) as inside_count,
                       SUM(CASE WHEN passes.checkin_time IS NOT NULL AND passes.checkout_time IS NOT NULL THEN 1 ELSE 0 END) as out_count
                FROM passes 
                JOIN appointments ON passes.appointment_id = appointments.id
                WHERE 1 $host_only_clause $date_sql
                GROUP BY DATE(appointments.appointment_time)
                ORDER BY DATE(appointments.appointment_time)";
$chart_result = $conn->query($chart_query);

$chart_labels = [];
$chart_waiting = [];
$chart_inside = [];
$chart_out = [];
while ($row = $chart_result->fetch_assoc()) {
  $chart_labels[] = date('M d', strtotime($row['appt_date']));
  $chart_waiting[] = intval($row['waiting_count']);
  $chart_inside[] = intval($row['inside_count']);
  $chart_out[] = intval($row['out_count']);
}

$breadcrumbs = [];
echo erp_header('Dashboard', $breadcrumbs);
?>

<style>
  .erp-stats-grid {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
  }

  .erp-stat-card {
    flex: 1;
    padding: 10px 12px;
    font-size: 14px; /* Main text size */
    line-height: 1.2;
    cursor: pointer;
    text-align: center;
    border-radius: 5px;
    background: #f7f7f7;
    transition: transform 0.2s;
  }

  .erp-stat-card:hover {
    transform: translateY(-2px);
  }

  .erp-stat-card .stat-value {
    font-size: 24px; /* Larger font for the count value */
    font-weight: bold;
    margin-bottom: 4px;
  }

  .erp-stat-card .stat-label {
    font-size: 16px; /* Adjust this size for the label */
    color: #555;
  }

  #appointmentTrendsChart {
    max-height: 300px;
  }

  form.row.g-3.mb-4 {
    margin-bottom: 10px;
  }

  .erp-form-label {
    font-size: 13px;
    margin-bottom: 2px;
  }

  .erp-form-control {
    font-size: 13px;
    padding: 3px 6px;
  }

  .popup-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .popup-table th,
  .popup-table td {
    padding: 6px 4px;
    border: 1px solid #ddd;
    text-align: center;
  }

  .popup-table th {
    background: #f1f1f1;
  }
</style>

<!-- Cards -->
<div class="erp-stats-grid">
  <div class="erp-stat-card erp-click-card" data-status="inside">
    <div class="stat-value"><?= $inside ?></div>
    <div class="stat-label">Currently Inside</div>
  </div>
  <div class="erp-stat-card erp-click-card" data-status="waiting">
    <div class="stat-value"><?= $waiting ?></div>
    <div class="stat-label">Pending Visits</div>
  </div>
  <div class="erp-stat-card erp-click-card" data-status="out">
    <div class="stat-value"><?= $checked_out ?></div>
    <div class="stat-label">Checked Out</div>
  </div>
</div>

<!-- Filters -->
<form method="GET" class="row g-3 mb-2">
  <div class="col-md-2">
    <label class="erp-form-label">Date From</label>
    <input type="datetime-local" id="from" name="from" class="erp-form-control"
      value="<?= htmlspecialchars($from_input) ?>">
  </div>
  <div class="col-md-2">
    <label class="erp-form-label">Date To</label>
    <input type="datetime-local" id="to" name="to" class="erp-form-control" value="<?= htmlspecialchars($to_input) ?>">
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button type="submit" class="erp-btn erp-btn-primary w-100">Search</button>
  </div>
</form>

<!-- Chart Type Dropdown -->
<div class="mb-2" style="display:flex; justify-content:flex-end; gap:5px; align-items:center;">
  <label for="chartType" style="font-size:13px;">Chart Type:</label>
  <select id="chartType" class="erp-form-control" style="width:120px; font-size:13px;">
    <option value="bar" selected>Bar</option>
    <option value="line">Line</option>
    <option value="pie">Pie</option>
    <option value="doughnut">Doughnut</option>
  </select>
</div>

<!-- Chart -->
<div class="mb-3">
  <canvas id="appointmentTrendsChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const records = <?= json_encode($all_records) ?>;

  document.addEventListener("DOMContentLoaded", function () {
    // Auto-close datetime pickers
    ['from', 'to'].forEach(id => { document.getElementById(id).addEventListener('input', e => e.target.blur()); });

    // Card click popup
    document.querySelectorAll('.erp-click-card').forEach(card => {
      card.addEventListener('click', () => {
        const status = card.getAttribute('data-status');
        let filtered = status === 'out' ? records.filter(r => r.checkin_time && r.checkout_time)
          : records.filter(r => r.status === status);

        if (!filtered.length) {
          Swal.fire('No records', `No visitors with status "${status}" found.`, 'info');
          return;
        }

        let html = `<div style="max-height:350px; overflow:auto;">
                    <table class="popup-table">
                      <thead>
                        <tr><th>ID</th><th>Name</th><th>Company</th><th>Mobile</th><th>Appointment</th></tr>
                      </thead><tbody>`;
        filtered.forEach(r => { html += `<tr><td>${r.id}</td><td>${r.visitor_name}</td><td>${r.company}</td><td>${r.mobile}</td><td>${r.appointment_time}</td></tr>`; });
        html += `</tbody></table></div>`;

        Swal.fire({ title: `Visitors: ${status}`, html: html, width: '700px', showCloseButton: true, confirmButtonText: 'Close' });
      });
    });

    // Chart
    const ctx = document.getElementById('appointmentTrendsChart').getContext('2d');
    const chartData = {
      labels: <?= json_encode($chart_labels) ?>,
      datasets: [
        { label: 'Waiting', data: <?= json_encode($chart_waiting) ?>, backgroundColor: 'orange' },
        { label: 'Inside', data: <?= json_encode($chart_inside) ?>, backgroundColor: 'green' },
        { label: 'Out', data: <?= json_encode($chart_out) ?>, backgroundColor: 'red' }
      ]
    };

    let chartType = 'bar';
    let chart = new Chart(ctx, { type: chartType, data: chartData, options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } } });

    document.getElementById('chartType').addEventListener('change', function () {
      chartType = this.value;
      chart.destroy();
      chart = new Chart(ctx, { type: chartType, data: chartData, options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } } });
    });
  });
</script>

<?php echo erp_footer(); ?>
