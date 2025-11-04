<?php
// Manage_users.php
if (session_status() == PHP_SESSION_NONE)
    session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

// Only admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: Manage_users.php");
    exit;
}

// --- FETCH STATS ---
function fetch_count($conn, $query)
{
    $result = $conn->query($query);
    return ($result && $result !== false) ? $result->fetch_assoc()['total'] : 0;
}

// Users stats
$total_users = fetch_count($conn, "SELECT COUNT(*) as total FROM users");
$active_users = fetch_count($conn, "SELECT COUNT(*) as total FROM users WHERE status='Active'");
$admin_users = fetch_count($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='Active'");
$host_users = fetch_count($conn, "SELECT COUNT(*) as total FROM users WHERE role='host' AND status='Active'");
$security_users = fetch_count($conn, "SELECT COUNT(*) as total FROM users WHERE role='security' AND status='Active'");

// Appointments stats
$total_appointments = fetch_count($conn, "SELECT COUNT(*) as total FROM appointments");
$today_appointments = fetch_count($conn, "SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_time)=CURDATE()");
$pending_appointments = fetch_count($conn, "SELECT COUNT(*) as total FROM appointments a JOIN passes p ON a.id=p.appointment_id WHERE p.status='waiting'");
$completed_appointments = fetch_count($conn, "SELECT COUNT(*) as total FROM appointments a JOIN passes p ON a.id=p.appointment_id WHERE p.status='out'");

// Monthly users (last 12 months)
$monthly_users = [];
$has_created_at = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    if ($has_created_at && $has_created_at->num_rows > 0) {
        $count_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'");
    } else {
        $count_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE id>0");
    }
    $count = ($count_query && $count_query !== false) ? $count_query->fetch_assoc()['total'] : 0;
    $monthly_users[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => $count];
}

// Role distribution
$role_distribution_result = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE status='Active' GROUP BY role");
$role_distribution = ($role_distribution_result && $role_distribution_result !== false) ? $role_distribution_result->fetch_all(MYSQLI_ASSOC) : [];

// Appointment trends last 7 days
$appointment_trends = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count_query = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_time)='$date'");
    $count = ($count_query && $count_query !== false) ? $count_query->fetch_assoc()['total'] : 0;
    $appointment_trends[] = ['date' => date('M d', strtotime("-$i days")), 'count' => $count];
}

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Manage Users', 'url' => '', 'icon' => 'users']
];

echo erp_header('Manage Users', $breadcrumbs);
?>

<!-- STYLISH STATS CARDS (Clickable ERP-style) -->
<style>
    .erp-stats-grid {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
    }

    .erp-stat-card {
        flex: 1;
        padding: 10px 12px;
        font-size: 14px;
        line-height: 1.2;
        cursor: pointer;
        text-align: center;
        border-radius: 5px;
        background: #f7f7f7;
        transition: transform 0.2s;
    }

    .erp-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .erp-stat-card .stat-value {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 3px;
    }

    .erp-stat-card .stat-label {
        font-size: 12px;
        color: #555;
    }

    .popup-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .popup-table th,
    .popup-table td {
        padding: 5px;
        border: 1px solid #ddd;
        text-align: center;
    }

    .popup-table th {
        background: #f1f1f1;
    }
</style>

<div class="erp-stats-grid">
    <div class="erp-stat-card erp-click-card" data-type="total_users">
        <div class="stat-value"><?= $total_users ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="erp-stat-card erp-click-card" data-type="total_appointments">
        <div class="stat-value"><?= $total_appointments ?></div>
        <div class="stat-label">Appointments</div>
    </div>
    <div class="erp-stat-card erp-click-card" data-type="pending_appointments">
        <div class="stat-value"><?= $pending_appointments ?></div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<?php
// Prepare data arrays for popup tables
$allUsers = [];
$user_result = $conn->query("SELECT * FROM users ORDER BY id DESC");
while ($row = $user_result->fetch_assoc())
    $allUsers[] = $row;

$allAppointments = [];
$app_result = $conn->query("SELECT a.*, p.status AS pass_status FROM appointments a LEFT JOIN passes p ON a.id=p.appointment_id ORDER BY a.id DESC");
while ($row = $app_result->fetch_assoc())
    $allAppointments[] = $row;
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const allUsersData = <?= json_encode($allUsers) ?>;
    const allAppointmentsData = <?= json_encode($allAppointments) ?>;

    document.querySelectorAll('.erp-click-card').forEach(card => {
        card.addEventListener('click', () => {
            const type = card.getAttribute('data-type');
            let html = '', filtered = [];

            if (type === 'total_users') filtered = allUsersData;
            else if (type === 'active_users') filtered = allUsersData.filter(u => u.status === 'Active');
            else if (type === 'total_appointments') filtered = allAppointmentsData;
            else if (type === 'pending_appointments') filtered = allAppointmentsData.filter(a => a.pass_status === 'waiting');
            else if (type === 'completed_appointments') filtered = allAppointmentsData.filter(a => a.pass_status === 'out');

            if (type.includes('users')) {
                html = `<div style="max-height:350px; overflow:auto;"><table class="popup-table"><thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody>`;
                filtered.forEach(u => {
                    html += `<tr><td>${u.id}</td><td>${u.username || u.name || '-'}</td><td>${u.email || '-'}</td><td>${u.role}</td><td>${u.status}</td></tr>`;
                });
                html += `</tbody></table></div>`;
            } else {
                html = `<div style="max-height:350px; overflow:auto;"><table class="popup-table"><thead>
                    <tr><th>ID</th><th>Visitor Name</th><th>Appointment Time</th><th>Status</th></tr></thead><tbody>`;
                filtered.forEach(a => {
                    html += `<tr><td>${a.id}</td><td>${a.visitor_name || '-'}</td><td>${a.appointment_time || '-'}</td><td>${a.pass_status || '-'}</td></tr>`;
                });
                html += `</tbody></table></div>`;
            }

            Swal.fire({
                title: `Records: ${card.querySelector('.stat-label').innerText}`,
                html: html,
                width: '750px',
                showCloseButton: true,
                confirmButtonText: 'Close'
            });
        });
    });
</script>

<!-- DASHBOARD CHARTS -->
<div class="row g-3 my-3">

    <!-- Left Side Charts -->
    <div class="col-lg-8">
        <div class="erp-card p-3 mb-3" style="margin-bottom:1.5rem;">
            <h5><i class="fas fa-chart-line"></i> User Registration Trends</h5>
            <canvas id="userRegistrationChart" height="80"></canvas>
        </div>

        <div class="erp-card p-3" style="margin-top:1rem;">
            <h5><i class="fas fa-calendar-alt"></i> Appointment Trends (Last 7 Days)</h5>
            <canvas id="appointmentTrendsChart" height="180" style="width:100%; margin-top:0.5rem;"></canvas>
        </div>
    </div>

    <!-- Right Side Overview + Role Distribution -->
    <div class="col-lg-4 d-flex flex-column gap-3">
        <!-- System Overview -->
        <div class="erp-card p-3">
            <h5><i class="fas fa-tachometer-alt"></i> System Overview</h5>
            <div class="d-flex flex-wrap gap-1 justify-content-between mt-2">
                <div class="text-center flex-fill bg-light rounded py-1 px-1">
                    <div class="h6 text-primary mb-0"><?= $admin_users ?></div>
                    <small>Admins</small>
                </div>
                <div class="text-center flex-fill bg-light rounded py-1 px-1">
                    <div class="h6 text-info mb-0"><?= $host_users ?></div>
                    <small>Hosts</small>
                </div>
                <div class="text-center flex-fill bg-light rounded py-1 px-1">
                    <div class="h6 text-danger mb-0"><?= $security_users ?></div>
                    <small>Security</small>
                </div>
                <div class="text-center flex-fill bg-light rounded py-1 px-1">
                    <div class="h6 text-success mb-0"><?= $active_users ?></div>
                    <small>Active</small>
                </div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="erp-card p-3">
            <h5 style="font-size: 0.95rem;"><i class="fas fa-chart-pie"></i> Role Distribution</h5>
            <canvas id="roleDistributionChart" height="140"></canvas>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        new Chart(document.getElementById('userRegistrationChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthly_users, 'month')) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode(array_column($monthly_users, 'count')) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('roleDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($role_distribution, 'role')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($role_distribution, 'count')) ?>,
                    backgroundColor: ['#f59e0b', '#06b6d4', '#ef4444', '#10b981'],
                    cutout: '60%'
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
        });

        new Chart(document.getElementById('appointmentTrendsChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($appointment_trends, 'date')) ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?= json_encode(array_column($appointment_trends, 'count')) ?>,
                    backgroundColor: 'rgba(16,185,129,0.8)',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

    });
</script>

<?php echo erp_footer(); ?>