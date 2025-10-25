<?php
// ERP Layout System - Professional Enterprise Resource Planning UI
require_once __DIR__ . '/theme.php';

function erp_header($title, $breadcrumbs = [])
{
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - Visitor System</title>

        <!-- Modern ERP CSS Framework -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Chart.js for Analytics -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- DataTables for Advanced Tables -->
        <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <!-- SweetAlert2 for Modern Alerts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?php echo theme_css_vars(); ?>

        <style>
            :root {
                --erp-primary: var(--primary-color, #2563eb);
                --erp-secondary: var(--secondary-color, #1d4ed8);
                --erp-accent: #f59e0b;
                --erp-success: #10b981;
                --erp-warning: #f59e0b;
                --erp-danger: #ef4444;
                --erp-info: #06b6d4;
                --erp-light: #f8fafc;
                --erp-dark: #1e293b;
                --erp-gray-50: #f9fafb;
                --erp-gray-100: #f3f4f6;
                --erp-gray-200: #e5e7eb;
                --erp-gray-300: #d1d5db;
                --erp-gray-400: #9ca3af;
                --erp-gray-500: #6b7280;
                --erp-gray-600: #4b5563;
                --erp-gray-700: #374151;
                --erp-gray-800: #1f2937;
                --erp-gray-900: #111827;
                --erp-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                --erp-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
                --erp-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                --erp-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                --erp-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
                --erp-radius: 0.75rem;
                --erp-radius-sm: 0.5rem;
                --erp-radius-lg: 1rem;
            }

            * {
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: var(--erp-gray-50);
                color: var(--erp-gray-800);
                line-height: 1.6;
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }

            /* ERP Navigation */
            .erp-navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 64px;
                background: linear-gradient(135deg, var(--erp-primary) 0%, var(--erp-secondary) 100%);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                z-index: 1050;
                display: flex;
                align-items: center;
                padding: 0 1.5rem;
                box-shadow: var(--erp-shadow-lg);
            }

            .erp-navbar-brand {
                display: flex;
                align-items: center;
                color: white;
                text-decoration: none;
                font-weight: 700;
                font-size: 1.25rem;
                margin-right: 2rem;
            }

            .erp-navbar-brand img {
                height: 40px;
                width: auto;
                border-radius: var(--erp-radius-sm);
                margin-right: 0.75rem;
                object-fit: cover;
            }

            .erp-navbar-toggle {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: var(--erp-radius-sm);
                transition: all 0.2s;
                margin-right: 1rem;
            }

            .erp-navbar-toggle:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            .erp-navbar-actions {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .erp-user-menu {
                position: relative;
            }

            .erp-user-avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
            }

            .erp-user-avatar:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            /* ERP Sidebar */
            .erp-sidebar {
                position: fixed;
                top: 64px;
                left: 0;
                width: 280px;
                height: calc(100vh - 64px);
                background: white;
                border-right: 1px solid var(--erp-gray-200);
                overflow-y: auto;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1040;
                box-shadow: var(--erp-shadow-lg);
            }

            .erp-sidebar.collapsed {
                width: 80px;
            }

            .erp-sidebar-header {
                padding: 1.5rem 1rem;
                border-bottom: 1px solid var(--erp-gray-200);
                background: var(--erp-gray-50);
            }

            .erp-sidebar-title {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--erp-gray-500);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin: 0;
            }

            .erp-sidebar-nav {
                padding: 1rem 0;
            }

            .erp-nav-item {
                margin: 0.25rem 0.75rem;
            }

            .erp-nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: var(--erp-gray-700);
                text-decoration: none;
                border-radius: var(--erp-radius);
                transition: all 0.2s;
                font-weight: 500;
                position: relative;
            }

            .erp-nav-link:hover {
                background: var(--erp-gray-100);
                color: var(--erp-primary);
                transform: translateX(2px);
            }

            .erp-nav-link.active {
                background: linear-gradient(135deg, var(--erp-primary) 0%, var(--erp-secondary) 100%);
                color: white;
                box-shadow: var(--erp-shadow-md);
            }

            .erp-nav-link.active::before {
                content: '';
                position: absolute;
                left: -0.75rem;
                top: 50%;
                transform: translateY(-50%);
                width: 4px;
                height: 20px;
                background: var(--erp-primary);
                border-radius: 2px;
            }

            .erp-nav-icon {
                width: 20px;
                height: 20px;
                margin-right: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.125rem;
            }

            .erp-nav-text {
                transition: opacity 0.2s;
            }

            .erp-sidebar.collapsed .erp-nav-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
            }

            .erp-sidebar.collapsed .erp-nav-link {
                justify-content: center;
                padding: 0.75rem;
            }

            .erp-sidebar.collapsed .erp-nav-icon {
                margin-right: 0;
            }

            /* ERP Main Content */
            .erp-main-content {
                margin-left: 280px;
                margin-top: 64px;
                min-height: calc(100vh - 64px);
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                background: var(--erp-gray-50);
            }

            .erp-main-content.expanded {
                margin-left: 80px;
            }

            .erp-content-wrapper {
                padding: 2rem;
                max-width: 1400px;
                margin: 0 auto;
            }

            /* ERP Page Header */
            .erp-page-header {
                background: white;
                border-radius: var(--erp-radius-lg);
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: var(--erp-shadow);
                border: 1px solid var(--erp-gray-200);
            }

            .erp-page-title {
                font-size: 2rem;
                font-weight: 700;
                color: var(--erp-gray-900);
                margin: 0 0 0.5rem 0;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .erp-page-title i {
                color: var(--erp-primary);
                font-size: 1.75rem;
            }

            .erp-breadcrumb {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--erp-gray-500);
                font-size: 0.875rem;
            }

            .erp-breadcrumb a {
                color: var(--erp-primary);
                text-decoration: none;
                transition: color 0.2s;
            }

            .erp-breadcrumb a:hover {
                color: var(--erp-secondary);
            }

            /* ERP Cards */
            .erp-card {
                background: white;
                border-radius: var(--erp-radius-lg);
                padding: 1.5rem;
                box-shadow: var(--erp-shadow);
                border: 1px solid var(--erp-gray-200);
                transition: all 0.2s;
                margin-bottom: 1.5rem;
            }

            .erp-card:hover {
                box-shadow: var(--erp-shadow-lg);
                transform: translateY(-2px);
            }

            .erp-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--erp-gray-200);
            }

            .erp-card-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--erp-gray-900);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .erp-card-title i {
                color: var(--erp-primary);
            }

            /* ERP Stats Cards */
            .erp-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .erp-stat-card {
                background: white;
                border-radius: var(--erp-radius-lg);
                padding: 1.5rem;
                box-shadow: var(--erp-shadow);
                border: 1px solid var(--erp-gray-200);
                transition: all 0.2s;
                position: relative;
                overflow: hidden;
            }

            .erp-stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, var(--erp-primary), var(--erp-secondary));
            }

            .erp-stat-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--erp-shadow-xl);
            }

            .erp-stat-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1rem;
            }

            .erp-stat-icon {
                width: 48px;
                height: 48px;
                border-radius: var(--erp-radius);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                color: white;
            }

            .erp-stat-icon.primary {
                background: var(--erp-primary);
            }

            .erp-stat-icon.success {
                background: var(--erp-success);
            }

            .erp-stat-icon.warning {
                background: var(--erp-warning);
            }

            .erp-stat-icon.danger {
                background: var(--erp-danger);
            }

            .erp-stat-icon.info {
                background: var(--erp-info);
            }

            .erp-stat-value {
                font-size: 2.5rem;
                font-weight: 700;
                color: var(--erp-gray-900);
                margin: 0;
                line-height: 1;
            }

            .erp-stat-label {
                font-size: 0.875rem;
                color: var(--erp-gray-500);
                margin: 0.5rem 0 0 0;
                font-weight: 500;
            }

            .erp-stat-change {
                font-size: 0.75rem;
                font-weight: 600;
                padding: 0.25rem 0.5rem;
                border-radius: var(--erp-radius-sm);
                margin-top: 0.5rem;
                display: inline-block;
            }

            .erp-stat-change.positive {
                background: rgba(16, 185, 129, 0.1);
                color: var(--erp-success);
            }

            .erp-stat-change.negative {
                background: rgba(239, 68, 68, 0.1);
                color: var(--erp-danger);
            }

            /* ERP Tables */
            .erp-table-container {
                background: white;
                border-radius: var(--erp-radius-lg);
                overflow: hidden;
                box-shadow: var(--erp-shadow);
                border: 1px solid var(--erp-gray-200);
            }

            .erp-table {
                width: 100%;
                border-collapse: collapse;
                margin: 0;
            }

            .erp-table thead {
                background: var(--erp-gray-50);
            }

            .erp-table th {
                padding: 1rem 1.5rem;
                text-align: left;
                font-weight: 600;
                color: var(--erp-gray-700);
                border-bottom: 1px solid var(--erp-gray-200);
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .erp-table td {
                padding: 1rem 1.5rem;
                border-bottom: 1px solid var(--erp-gray-100);
                color: var(--erp-gray-800);
            }

            .erp-table tbody tr {
                transition: all 0.2s;
            }

            .erp-table tbody tr:hover {
                background: var(--erp-gray-50);
            }

            .erp-table tbody tr:last-child td {
                border-bottom: none;
            }

            /* ERP Buttons */
            .erp-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: var(--erp-radius);
                font-weight: 600;
                font-size: 0.875rem;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.2s;
                position: relative;
                overflow: hidden;
            }

            .erp-btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s;
            }

            .erp-btn:hover::before {
                left: 100%;
            }

            .erp-btn-primary {
                background: linear-gradient(135deg, var(--erp-primary) 0%, var(--erp-secondary) 100%);
                color: white;
                box-shadow: var(--erp-shadow-md);
            }

            .erp-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: var(--erp-shadow-lg);
            }

            .erp-btn-secondary {
                background: var(--erp-gray-100);
                color: var(--erp-gray-700);
                border: 1px solid var(--erp-gray-300);
            }

            .erp-btn-secondary:hover {
                background: var(--erp-gray-200);
                transform: translateY(-1px);
            }

            .erp-btn-success {
                background: var(--erp-success);
                color: white;
            }

            .erp-btn-warning {
                background: var(--erp-warning);
                color: white;
            }

            .erp-btn-danger {
                background: var(--erp-danger);
                color: white;
            }

            .erp-btn-sm {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }

            .erp-btn-lg {
                padding: 1rem 2rem;
                font-size: 1rem;
            }

            /* ERP Forms */
            .erp-form-group {
                margin-bottom: 1.5rem;
            }

            .erp-form-label {
                display: block;
                font-weight: 600;
                color: var(--erp-gray-700);
                margin-bottom: 0.5rem;
                font-size: 0.875rem;
            }

            .erp-form-control {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1px solid var(--erp-gray-300);
                border-radius: var(--erp-radius);
                font-size: 0.875rem;
                transition: all 0.2s;
                background: white;
            }

            .erp-form-control:focus {
                outline: none;
                border-color: var(--erp-primary);
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            }

            .erp-form-control::placeholder {
                color: var(--erp-gray-400);
            }

            /* ERP Badges */
            .erp-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.75rem;
                border-radius: var(--erp-radius-sm);
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .erp-badge-primary {
                background: rgba(37, 99, 235, 0.1);
                color: var(--erp-primary);
            }

            .erp-badge-success {
                background: rgba(16, 185, 129, 0.1);
                color: var(--erp-success);
            }

            .erp-badge-warning {
                background: rgba(245, 158, 11, 0.1);
                color: var(--erp-warning);
            }

            .erp-badge-danger {
                background: rgba(239, 68, 68, 0.1);
                color: var(--erp-danger);
            }

            .erp-badge-info {
                background: rgba(6, 182, 212, 0.1);
                color: var(--erp-info);
            }

            /* ERP Alerts */
            .erp-alert {
                padding: 1rem 1.5rem;
                border-radius: var(--erp-radius);
                margin-bottom: 1.5rem;
                border: 1px solid;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .erp-alert-success {
                background: rgba(16, 185, 129, 0.1);
                border-color: var(--erp-success);
                color: var(--erp-success);
            }

            .erp-alert-danger {
                background: rgba(239, 68, 68, 0.1);
                border-color: var(--erp-danger);
                color: var(--erp-danger);
            }

            .erp-alert-warning {
                background: rgba(245, 158, 11, 0.1);
                border-color: var(--erp-warning);
                color: var(--erp-warning);
            }

            .erp-alert-info {
                background: rgba(6, 182, 212, 0.1);
                border-color: var(--erp-info);
                color: var(--erp-info);
            }

            /* Responsive Design */
            @media (max-width: 1024px) {
                .erp-sidebar {
                    transform: translateX(-100%);
                }

                .erp-sidebar.show {
                    transform: translateX(0);
                }

                .erp-main-content {
                    margin-left: 0;
                }

                .erp-content-wrapper {
                    padding: 1rem;
                }

                .erp-stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 768px) {
                .erp-navbar {
                    padding: 0 1rem;
                }

                .erp-page-header {
                    padding: 1.5rem;
                }

                .erp-page-title {
                    font-size: 1.5rem;
                }

                .erp-card {
                    padding: 1rem;
                }

                .erp-table th,
                .erp-table td {
                    padding: 0.75rem 1rem;
                }
            }

            /* Loading States */
            .erp-loading {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid var(--erp-gray-300);
                border-radius: 50%;
                border-top-color: var(--erp-primary);
                animation: spin 1s ease-in-out infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /* Dark Mode Support */
            @media (prefers-color-scheme: dark) {
                :root {
                    --erp-gray-50: #1f2937;
                    --erp-gray-100: #374151;
                    --erp-gray-200: #4b5563;
                    --erp-gray-300: #6b7280;
                    --erp-gray-400: #9ca3af;
                    --erp-gray-500: #d1d5db;
                    --erp-gray-600: #e5e7eb;
                    --erp-gray-700: #f3f4f6;
                    --erp-gray-800: #f9fafb;
                    --erp-gray-900: #ffffff;
                }
            }
        </style>
    </head>

    <body>
        <!-- ERP Navigation -->
        <nav class="erp-navbar">
            <button class="erp-navbar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <a href="#" class="erp-navbar-brand">
                <img src="../<?php echo theme_logo(); ?>" alt="ERP Logo">
                <span class="d-none d-md-inline">Visitor System</span>
            </a>

            <div class="erp-navbar-actions">
                <div class="erp-user-menu">
                    <div class="erp-user-avatar" onclick="toggleUserMenu()">
                        <?php echo strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)); ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ERP Sidebar -->
        <aside class="erp-sidebar" id="sidebar">
            <nav class="erp-sidebar-nav">
                <?php if (isset($_SESSION['user']['role'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <div class="erp-nav-item">
                            <a href="dashboard.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-tachometer-alt"></i>
                                <span class="erp-nav-text">Dashboard</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="manage_users.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-tachometer-alt"></i>
                                <span class="erp-nav-text">manage Users</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="User_creation.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'User_creation.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-users"></i>
                                <span class="erp-nav-text">User Management</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="create_company.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'create_company.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-building"></i>
                                <span class="erp-nav-text">Companies</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="create_department.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'create_department.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-sitemap"></i>
                                <span class="erp-nav-text">Departments</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="appearance.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appearance.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-palette"></i>
                                <span class="erp-nav-text">Appearance</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="reports.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-chart-bar"></i>
                                <span class="erp-nav-text">Reports</span>
                            </a>
                        </div>
                    <?php elseif ($_SESSION['user']['role'] === 'security'): ?>
                        <div class="erp-nav-item">
                            <a href="dashboard.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-tachometer-alt"></i>
                                <span class="erp-nav-text">Dashboard</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="track_visitors.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'track_visitors.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-user-check"></i>
                                <span class="erp-nav-text">Visitor Tracking</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="reports.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-chart-bar"></i>
                                <span class="erp-nav-text">Reports</span>
                            </a>
                        </div>
                    <?php elseif ($_SESSION['user']['role'] === 'host'): ?>
                        <div class="erp-nav-item">
                            <a href="dashboard.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-tachometer-alt"></i>
                                <span class="erp-nav-text">Dashboard</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="book_appointment.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'book_appointment.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-calendar-plus"></i>
                                <span class="erp-nav-text">Book Appointment</span>
                            </a>
                        </div>
                        <div class="erp-nav-item">
                            <a href="reports.php"
                                class="erp-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                                <i class="erp-nav-icon fas fa-chart-bar"></i>
                                <span class="erp-nav-text">Reports</span>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="erp-nav-item">
                    <a href="../pages/logout.php" class="erp-nav-link">
                        <i class="erp-nav-icon fas fa-sign-out-alt"></i>
                        <span class="erp-nav-text">Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- ERP Main Content -->
        <main class="erp-main-content" id="mainContent">
            <div class="erp-content-wrapper">
                <!-- Page Header -->
                <div class="erp-page-header">
                    <h1 class="erp-page-title">
                        <i class="fas fa-<?php echo $breadcrumbs[0]['icon'] ?? 'home'; ?>"></i>
                        <?php echo htmlspecialchars($title); ?>
                    </h1>
                    <?php if (!empty($breadcrumbs)): ?>
                        <nav class="erp-breadcrumb">
                            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                <?php if ($index > 0): ?>
                                    <i class="fas fa-chevron-right"></i>
                                <?php endif; ?>
                                <?php if (isset($crumb['url'])): ?>
                                    <a
                                        href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['title']); ?></a>
                                <?php else: ?>
                                    <span><?php echo htmlspecialchars($crumb['title']); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </div>
                <?php
                return ob_get_clean();
}

function erp_footer()
{
    ob_start();
    ?>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <!-- ERP JavaScript -->
        <script>
            // Sidebar Toggle
            document.getElementById('sidebarToggle').addEventListener('click', function () {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');

                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');

                // Store preference
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore sidebar state
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            });

            // Mobile sidebar toggle
            function toggleMobileSidebar() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
            }

            // User menu toggle
            function toggleUserMenu() {
                // Implementation for user dropdown menu
                console.log('User menu clicked');
            }

            // Auto-hide alerts
            document.addEventListener('DOMContentLoaded', function () {
                const alerts = document.querySelectorAll('.erp-alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }, 5000);
                });
            });

            // Initialize DataTables
            document.addEventListener('DOMContentLoaded', function () {
                const tables = document.querySelectorAll('.erp-table[data-datatable]');
                tables.forEach(table => {
                    new DataTable(table, {
                        responsive: true,
                        pageLength: 25,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                });
            });

            // Form validation
            function validateForm(form) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                return isValid;
            }

            // Add loading state to buttons
            function setButtonLoading(button, loading = true) {
                if (loading) {
                    button.disabled = true;
                    button.innerHTML = '<span class="erp-loading"></span> Loading...';
                } else {
                    button.disabled = false;
                    button.innerHTML = button.getAttribute('data-original-text') || 'Submit';
                }
            }

            // Store original button text
            document.addEventListener('DOMContentLoaded', function () {
                const buttons = document.querySelectorAll('.erp-btn');
                buttons.forEach(button => {
                    button.setAttribute('data-original-text', button.innerHTML);
                });
            });

            // SweetAlert2 Utility Functions
            window.erpConfirm = function (title, text, confirmButtonText = 'Yes, proceed!', cancelButtonText = 'Cancel') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText,
                    reverseButtons: true
                });
            };

            window.erpSuccess = function (title, text = '') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                });
            };

            window.erpError = function (title, text = '') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            };

            window.erpInfo = function (title, text = '') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'info',
                    confirmButtonColor: '#06b6d4'
                });
            };

            // Enhanced delete confirmation
            window.erpDeleteConfirm = function (url, itemName = 'item') {
                return erpConfirm(
                    'Are you sure?',
                    `You won't be able to revert this! This will permanently delete the ${itemName}.`,
                    'Yes, delete it!',
                    'Cancel'
                ).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            };

            // Enhanced edit confirmation
            window.erpEditConfirm = function (url, itemName = 'item') {
                return erpConfirm(
                    'Edit ' + itemName,
                    'Are you sure you want to edit this ' + itemName + '?',
                    'Yes, edit it!',
                    'Cancel'
                ).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            };
        </script>
    </body>

    </html>
    <?php
    return ob_get_clean();
}

// Helper functions for common ERP components
function erp_stat_card($icon, $value, $label, $change = null, $color = 'primary')
{
    $changeHtml = '';
    if ($change) {
        $changeClass = $change > 0 ? 'positive' : 'negative';
        $changeSymbol = $change > 0 ? '+' : '';
        $changeHtml = "<div class='erp-stat-change {$changeClass}'>{$changeSymbol}{$change}%</div>";
    }

    return "
    <div class='erp-stat-card'>
        <div class='erp-stat-header'>
            <div class='erp-stat-icon {$color}'>
                <i class='{$icon}'></i>
            </div>
        </div>
        <h3 class='erp-stat-value'>{$value}</h3>
        <p class='erp-stat-label'>{$label}</p>
        {$changeHtml}
    </div>";
}

function erp_alert($message, $type = 'info', $dismissible = true)
{
    $dismissBtn = $dismissible ? '<button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>' : '';
    return "
    <div class='erp-alert erp-alert-{$type}'>
        <i class='fas fa-" . ($type === 'success' ? 'check-circle' : ($type === 'danger' ? 'exclamation-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle'))) . "'></i>
        <span>{$message}</span>
        {$dismissBtn}
    </div>";
}

function erp_badge($text, $type = 'primary')
{
    return "<span class='erp-badge erp-badge-{$type}'>{$text}</span>";
}

function erp_button($text, $type = 'primary', $size = '', $icon = '', $attributes = '')
{
    $iconHtml = $icon ? "<i class='{$icon}'></i>" : '';
    return "<button class='erp-btn erp-btn-{$type} {$size}' {$attributes}>{$iconHtml}{$text}</button>";
}

function erp_link_button($text, $url, $type = 'primary', $size = '', $icon = '')
{
    $iconHtml = $icon ? "<i class='{$icon}'></i>" : '';
    return "<a href='{$url}' class='erp-btn erp-btn-{$type} {$size}'>{$iconHtml}{$text}</a>";
}
?>