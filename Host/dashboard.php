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
                             WHERE passes.status='out' $host_only_clause $date_sql")->fetch_assoc()['c'];

// Fetch records for popup (all)
$all_records = [];
$records_result = $conn->query("SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.appointment_time, passes.status 
                                FROM passes 
                                JOIN appointments ON passes.appointment_id=appointments.id
                                WHERE 1 $host_only_clause $date_sql
                                ORDER BY passes.id DESC");
while ($row = $records_result->fetch_assoc()) {
  $all_records[] = $row;
}

// Prepare chart data (Appointments trends per day)
$chart_query = "SELECT DATE(appointments.appointment_time) as appt_date, 
                       SUM(CASE WHEN passes.status='waiting' THEN 1 ELSE 0 END) as waiting_count,
                       SUM(CASE WHEN passes.status='inside' THEN 1 ELSE 0 END) as inside_count,
                       SUM(CASE WHEN passes.status='out' THEN 1 ELSE 0 END) as out_count
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

// Breadcrumbs
$breadcrumbs = [
  ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt']
];

echo erp_header('Dashboard', $breadcrumbs);
?>

<!-- Cards -->
<div class="erp-stats-grid">
  <div class="erp-stat-card erp-click-card" data-status="inside">
    <?= erp_stat_card('fas fa-user-check', $inside, 'Currently Inside', null, 'success'); ?>
  </div>
  <div class="erp-stat-card erp-click-card" data-status="waiting">
    <?= erp_stat_card('fas fa-clock', $waiting, 'Pending Visits', null, 'warning'); ?>
  </div>
  <div class="erp-stat-card erp-click-card" data-status="out">
    <?= erp_stat_card('fas fa-sign-out-alt', $checked_out, 'Checked Out', null, 'info'); ?>
  </div>
</div>

<!-- Filters -->
<form method="GET" class="row g-3 mb-4">
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

<!-- Appointment Trends Chart -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5>Appointment Trends (Chart)</h5>
    <select id="chartType" class="erp-form-control" style="width:150px;">
      <option value="bar" selected>Bar</option>
      <option value="line">Line</option>
      <option value="pie">Pie</option>
      <option value="doughnut">Doughnut</option>
    </select>
  </div>
  <canvas id="appointmentTrendsChart" style="width:100%; max-height:400px;"></canvas>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const records = <?= json_encode($all_records) ?>;

  document.addEventListener("DOMContentLoaded", function () {
    // Auto-close datetime pickers after selection
    ['from', 'to'].forEach(id => {
      const el = document.getElementById(id);
      el.addEventListener('input', () => el.blur());
    });

    // Cards click popup
    document.querySelectorAll('.erp-click-card').forEach(card => {
      card.addEventListener('click', () => {
        const status = card.getAttribute('data-status');
        const filtered = records.filter(r => r.status === status);
        if (filtered.length === 0) {
          Swal.fire('No records', `No visitors with status "${status}" found.`, 'info');
          return;
        }
        let html = `<table style="width:100%;border-collapse:collapse;">
                        <tr><th>ID</th><th>Name</th><th>Company</th><th>Mobile</th><th>Appointment Time</th></tr>`;
        filtered.forEach(r => {
          html += `<tr style="border-bottom:1px solid #ddd;">
                            <td>${r.id}</td>
                            <td>${r.visitor_name}</td>
                            <td>${r.company}</td>
                            <td>${r.mobile}</td>
                            <td>${r.appointment_time}</td>
                        </tr>`;
        });
        html += '</table>';
        Swal.fire({
          title: `Visitors: ${status}`,
          html: html,
          width: '700px',
          showCloseButton: true,
          confirmButtonText: 'Close'
        });
      });
    });

    // Appointment Trends Chart
    const ctx = document.getElementById('appointmentTrendsChart').getContext('2d');
    const chartData = {
      labels: <?= json_encode($chart_labels) ?>,
      datasets: [
        { label: 'Waiting', data: <?= json_encode($chart_waiting) ?>, backgroundColor: 'rgba(255, 159, 64, 0.7)' },
        { label: 'Inside', data: <?= json_encode($chart_inside) ?>, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
        { label: 'Out', data: <?= json_encode($chart_out) ?>, backgroundColor: 'rgba(75, 192, 192, 0.7)' }
      ]
    };

    let chartType = 'bar';
    let chart = new Chart(ctx, { type: chartType, data: chartData, options: getChartOptions(chartType) });

    document.getElementById('chartType').addEventListener('change', function () {
      chartType = this.value;
      chart.destroy();
      chart = new Chart(ctx, { type: chartType, data: chartData, options: getChartOptions(chartType) });
    });

    function getChartOptions(type) {
      const isPie = (type === 'pie' || type === 'doughnut');
      return {
        responsive: true,
        plugins: { legend: { display: true }, tooltip: { mode: 'index', intersect: false } },
        scales: isPie ? {} : {
          x: { stacked: true, title: { display: true, text: 'Date' } },
          y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Appointments' } }
        }
      };
    }
  });
</script>

<?php
echo erp_footer();
?>