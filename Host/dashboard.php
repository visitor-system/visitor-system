<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
  header("Location: ../pages/login.html");
  exit;
}

// Host-only filter
$host_only_clause = '';
if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'host') {
  $host_id = intval($_SESSION['user']['id']);
  $host_only_clause = " AND appointments.host_id = $host_id";
}

// Default From/To dates
$from_input = $_GET['from'] ?? date('Y-m-d\T00:00');
$to_input = $_GET['to'] ?? date('Y-m-d\TH:i');

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

// Pagination
$limit = $_GET['limit'] ?? 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Total records
$total_query = "SELECT COUNT(*) as total FROM passes 
                JOIN appointments ON passes.appointment_id = appointments.id
                WHERE 1 $host_only_clause $search_sql $status_sql $date_sql";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch records
$query = "SELECT passes.*, appointments.visitor_name, appointments.mobile, appointments.company, appointments.appointment_time
          FROM passes 
          JOIN appointments ON passes.appointment_id = appointments.id
          WHERE 1 $host_only_clause $search_sql $status_sql $date_sql
          ORDER BY passes.id DESC
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Prepare chart data (Appointments trends per day)
// Step 1: Generate date range array
$period = new DatePeriod(
  new DateTime($from_sql),
  new DateInterval('P1D'),
  (new DateTime($to_sql))->modify('+1 day')
);

$chart_labels = [];
$chart_waiting = [];
$chart_inside = [];
$chart_out = [];

foreach ($period as $date) {
  $chart_labels[$date->format('Y-m-d')] = $date->format('M d');
  $chart_waiting[$date->format('Y-m-d')] = 0;
  $chart_inside[$date->format('Y-m-d')] = 0;
  $chart_out[$date->format('Y-m-d')] = 0;
}

// Step 2: Fetch counts from database
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

// Step 3: Fill chart arrays
while ($row = $chart_result->fetch_assoc()) {
  $date = $row['appt_date'];
  if (isset($chart_labels[$date])) {
    $chart_waiting[$date] = intval($row['waiting_count']);
    $chart_inside[$date] = intval($row['inside_count']);
    $chart_out[$date] = intval($row['out_count']);
  }
}

// Convert associative arrays to indexed arrays for Chart.js
$chart_labels_js = array_values($chart_labels);
$chart_waiting_js = array_values($chart_waiting);
$chart_inside_js = array_values($chart_inside);
$chart_out_js = array_values($chart_out);

// Breadcrumbs
$breadcrumbs = [
  ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt']
];

echo erp_header('Dashboard', $breadcrumbs);
?>

<!-- Cards -->
<div class="erp-stats-grid">
  <?php
  echo erp_stat_card('fas fa-user-check', $inside, 'Currently Inside', null, 'success');
  echo erp_stat_card('fas fa-clock', $waiting, 'Pending Visits', null, 'warning');
  echo erp_stat_card('fas fa-sign-out-alt', $checked_out, 'Checked Out', null, 'info');
  ?>
</div>

<!-- Filters -->
<form method="GET" class="row g-3 mb-4">
  <div class="col-md-2">
    <label class="erp-form-label">Date From</label>
    <input type="datetime-local" name="from" class="erp-form-control" value="<?= htmlspecialchars($from_input) ?>">
  </div>
  <div class="col-md-2">
    <label class="erp-form-label">Date To</label>
    <input type="datetime-local" name="to" class="erp-form-control" value="<?= htmlspecialchars($to_input) ?>">
  </div>
  <div class="col-md-2">
    <label class="erp-form-label">Status</label>
    <select name="status" class="erp-form-control">
      <option value="">All</option>
      <option value="waiting" <?= $status_filter == 'waiting' ? 'selected' : '' ?>>Waiting</option>
      <option value="inside" <?= $status_filter == 'inside' ? 'selected' : '' ?>>Inside</option>
      <option value="out" <?= $status_filter == 'out' ? 'selected' : '' ?>>Out</option>
    </select>
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button type="submit" class="erp-btn erp-btn-primary w-100">Search</button>
  </div>
</form>

<!-- Appointment Trends Chart -->
<div class="mb-4">
  <h5>Appointment Trends (Bar Chart)</h5>
  <canvas id="appointmentTrendsChart" style="width:100%; max-height:400px;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('appointmentTrendsChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chart_labels_js) ?>,
        datasets: [
          {
            label: 'Waiting',
            data: <?= json_encode($chart_waiting_js) ?>,
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
          },
          {
            label: 'Inside',
            data: <?= json_encode($chart_inside_js) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
          },
          {
            label: 'Out',
            data: <?= json_encode($chart_out_js) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: { stacked: true, title: { display: true, text: 'Date' } },
          y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Appointments' } }
        }
      }
    });
  });
</script>