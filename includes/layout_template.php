<?php
session_start();
require_once __DIR__ . '/theme.php'; // make sure path is correct

$layout_title = '';

function start_layout($title = '')
{
    global $layout_title;
    $layout_title = $title;
    ob_start();
}

function end_layout()
{
    global $layout_title;
    $content = ob_get_clean();
    echo get_page_layout($layout_title ?: 'VP System', $content);
}

function get_page_layout($title, $content)
{
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>

        <!-- AdminLTE CSS -->
        <link rel="stylesheet" href="../assets/adminlte/css/adminlte.min.css">
        <link rel="stylesheet" href="../assets/adminlte/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="../assets/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">

        <?php echo theme_css_vars(); ?>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-light" style="background-color: var(--primary-color);">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars" style="color: var(--text-on-primary);"></i></a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <span class="nav-link" style="color: var(--text-on-primary); font-weight:500;">
                            <?php echo isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : ''; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt" style="color: var(--text-on-primary);"></i></a>
                    </li>
                </ul>
            </nav>

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: var(--primary-color);">
                <!-- Brand Logo -->
                <a href="dashboard.php" class="brand-link">
                    <img src="../<?php echo theme_logo(); ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                    <span class="brand-text font-weight-light">VP System</span>
                </a>

                <!-- Sidebar -->
                <div class="sidebar">
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <?php if (isset($_SESSION['user']['role'])): ?>
                                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                                    <li class="nav-item"><a href="User_creation.php" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Manage Users</p></a></li>
                                    <li class="nav-item"><a href="create_company.php" class="nav-link"><i class="nav-icon fas fa-building"></i><p>Companies</p></a></li>
                                    <li class="nav-item"><a href="create_department.php" class="nav-link"><i class="nav-icon fas fa-diagram-project"></i><p>Departments</p></a></li>
                                    <li class="nav-item"><a href="appearance.php" class="nav-link"><i class="nav-icon fas fa-palette"></i><p>Appearance</p></a></li>
                                    <li class="nav-item"><a href="reports.php" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Reports</p></a></li>
                                <?php elseif ($_SESSION['user']['role'] === 'security'): ?>
                                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                                    <li class="nav-item"><a href="track_visitors.php" class="nav-link"><i class="nav-icon fas fa-user-check"></i><p>Track Visitors</p></a></li>
                                    <li class="nav-item"><a href="reports.php" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Reports</p></a></li>
                                <?php elseif ($_SESSION['user']['role'] === 'host'): ?>
                                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                                    <li class="nav-item"><a href="book_appointment.php" class="nav-link"><i class="nav-icon fas fa-calendar-plus"></i><p>Appointments</p></a></li>
                                    <li class="nav-item"><a href="reports.php" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Reports</p></a></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper" style="background-color: var(--primary-bg);">
                <section class="content">
                    <div class="container-fluid pt-3">
                        <?php echo $content; ?>
                    </div>
                </section>
            </div>

            <!-- Footer -->
            <footer class="main-footer text-sm" style="background-color: var(--primary-color); color: var(--text-on-primary);">
                <div class="float-right d-none d-sm-inline">VP System</div>
                <strong>&copy; 2025 Pravas Pvt. Ltd.</strong>
            </footer>
        </div>

        <!-- AdminLTE Scripts -->
        <script src="../assets/adminlte/plugins/jquery/jquery.min.js"></script>
        <script src="../assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/adminlte/js/adminlte.min.js"></script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>
