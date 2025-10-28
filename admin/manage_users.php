<?php
// Manage_users.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

// Only admin can access
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

// No search filters needed for analytics dashboard

// Get user statistics with error handling
$total_users_result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = ($total_users_result && $total_users_result !== false) ? $total_users_result->fetch_assoc()['total'] : 0;

$active_users_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'Active'");
$active_users = ($active_users_result && $active_users_result !== false) ? $active_users_result->fetch_assoc()['total'] : 0;

$admin_users_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 'Active'");
$admin_users = ($admin_users_result && $admin_users_result !== false) ? $admin_users_result->fetch_assoc()['total'] : 0;

$host_users_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'host' AND status = 'Active'");
$host_users = ($host_users_result && $host_users_result !== false) ? $host_users_result->fetch_assoc()['total'] : 0;

$security_users_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'security' AND status = 'Active'");
$security_users = ($security_users_result && $security_users_result !== false) ? $security_users_result->fetch_assoc()['total'] : 0;

// Get appointment statistics with error handling
$total_appointments_result = $conn->query("SELECT COUNT(*) as total FROM appointments");
$total_appointments = ($total_appointments_result && $total_appointments_result !== false) ? $total_appointments_result->fetch_assoc()['total'] : 0;

$today_appointments_result = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_time) = CURDATE()");
$today_appointments = ($today_appointments_result && $today_appointments_result !== false) ? $today_appointments_result->fetch_assoc()['total'] : 0;

$pending_appointments_result = $conn->query("SELECT COUNT(*) as total FROM appointments a JOIN passes p ON a.id = p.appointment_id WHERE p.status = 'waiting'");
$pending_appointments = ($pending_appointments_result && $pending_appointments_result !== false) ? $pending_appointments_result->fetch_assoc()['total'] : 0;

$completed_appointments_result = $conn->query("SELECT COUNT(*) as total FROM appointments a JOIN passes p ON a.id = p.appointment_id WHERE p.status = 'out'");
$completed_appointments = ($completed_appointments_result && $completed_appointments_result !== false) ? $completed_appointments_result->fetch_assoc()['total'] : 0;

// Get monthly user registration data for chart
$monthly_users = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    // Check if created_at column exists, otherwise use id as proxy
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($result && $result->num_rows > 0) {
        $count_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    } else {
        // Fallback: use id as proxy for registration (assuming higher id = newer user)
        $count_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE id > 0");
    }

    if ($count_query && $count_query !== false) {
        $count = $count_query->fetch_assoc()['total'];
    } else {
        $count = 0;
    }
    $monthly_users[] = ['month' => date('M Y', strtotime("-$i months")), 'count' => $count];
}

// Get role distribution data
$role_distribution_result = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE status = 'Active' GROUP BY role");
if ($role_distribution_result && $role_distribution_result !== false) {
    $role_distribution = $role_distribution_result->fetch_all(MYSQLI_ASSOC);
} else {
    $role_distribution = [];
}

// Get appointment trends (last 7 days)
$appointment_trends = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count_query = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_time) = '$date'");
    if ($count_query && $count_query !== false) {
        $count = $count_query->fetch_assoc()['total'];
    } else {
        $count = 0;
    }
    $appointment_trends[] = ['date' => date('M d', strtotime("-$i days")), 'count' => $count];
}

// No user table needed for analytics dashboard

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Manage Users', 'url' => '', 'icon' => 'users']
];

echo erp_header('Manage Users', $breadcrumbs);
?>

<!-- ERP Stats Cards -->
<div class="erp-stats-grid">
    <?php echo erp_stat_card('fas fa-users', $total_users, 'Total Users', null, 'primary'); ?>
    <?php echo erp_stat_card('fas fa-user-check', $active_users, 'Active Users', null, 'success'); ?>
    <?php echo erp_stat_card('fas fa-calendar-check', $total_appointments, 'Total Appointments', null, 'info'); ?>
    <?php echo erp_stat_card('fas fa-calendar-day', $today_appointments, 'Today\'s Appointments', null, 'warning'); ?>
    <?php echo erp_stat_card('fas fa-clock', $pending_appointments, 'Pending Appointments', null, 'danger'); ?>
    <?php echo erp_stat_card('fas fa-check-circle', $completed_appointments, 'Completed Appointments', null, 'success'); ?>
</div>

<!-- Analytics Dashboard -->
<div class="row g-4 mb-4">
    <!-- User Registration Chart -->
    <div class="col-lg-8">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title">
                    <i class="fas fa-chart-line"></i>
                    User Registration Trends
                </h3>
            </div>
            <div class="p-3">
                <canvas id="userRegistrationChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Role Distribution Chart -->
    <div class="col-lg-4">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title">
                    <i class="fas fa-chart-pie"></i>
                    Role Distribution
                </h3>
            </div>
            <div class="p-3">
                <canvas id="roleDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Analytics -->
<div class="row g-4 mb-4">
    <!-- Appointment Trends -->
    <div class="col-lg-8">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title">
                    <i class="fas fa-chart-bar"></i>
                    Appointment Trends (Last 7 Days)
                </h3>
            </div>
            <div class="p-3">
                <canvas id="appointmentTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="erp-card">
            <div class="erp-card-header">
                <h3 class="erp-card-title">
                    <i class="fas fa-tachometer-alt"></i>
                    System Overview
                </h3>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-primary mb-1"><?= $admin_users ?></div>
                            <small class="text-muted">Admins</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-info mb-1"><?= $host_users ?></div>
                            <small class="text-muted">Hosts</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-danger mb-1"><?= $security_users ?></div>
                            <small class="text-muted">Security</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-success mb-1"><?= $active_users ?></div>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h3>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <?php echo erp_link_button('Add User', 'User_creation.php', 'primary', 'erp-btn-sm', 'fas fa-plus'); ?>
        </div>
        <div class="col-md-3">
            <?php echo erp_link_button('Manage Users', 'User_creation.php', 'success', 'erp-btn-sm', 'fas fa-users'); ?>
        </div>
        <div class="col-md-3">
            <?php echo erp_link_button('User Reports', 'reports.php', 'warning', 'erp-btn-sm', 'fas fa-chart-bar'); ?>
        </div>
        <div class="col-md-3">
            <?php echo erp_link_button('System Settings', 'appearance.php', 'info', 'erp-btn-sm', 'fas fa-cog'); ?>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // User Registration Chart
        const userRegCtx = document.getElementById('userRegistrationChart').getContext('2d');
        new Chart(userRegCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthly_users, 'month')) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode(array_column($monthly_users, 'count')) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Role Distribution Chart
        const roleDistCtx = document.getElementById('roleDistributionChart').getContext('2d');
        new Chart(roleDistCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($role_distribution, 'role')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($role_distribution, 'count')) ?>,
                    backgroundColor: [
                        '#f59e0b',
                        '#06b6d4',
                        '#ef4444',
                        '#10b981'
                    ],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Appointment Trends Chart
        const appointmentTrendsCtx = document.getElementById('appointmentTrendsChart').getContext('2d');
        new Chart(appointmentTrendsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($appointment_trends, 'date')) ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?= json_encode(array_column($appointment_trends, 'count')) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>

<?php echo erp_footer(); ?>