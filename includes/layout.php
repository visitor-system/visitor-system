<?php
// layout.php - header & footer for all pages with ERP-style layout

require_once __DIR__ . '/theme.php'; // Make sure path is correct

function page_header($title)
{
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <?php theme_css_vars(); ?>
        <style>
            body {
                background: var(--primary-bg);
                font-family: 'Segoe UI', sans-serif;
                color: var(--text-color, #222);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .navbar {
                background-color: var(--primary-color);
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                padding: 0 20px;
                height: var(--navbar-height, 60px);
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: fixed;
                width: 100%;
                z-index: 1000;
            }

            #toggleSidebar {
                font-size: 1.5rem;
                background: none;
                border: none;
                cursor: pointer;
                color: var(--text-on-primary);
            }

            .sidebar {
                position: fixed;
                top: var(--navbar-height, 60px);
                left: 0;
                width: var(--sidebar-width, 200px);
                height: calc(100% - var(--navbar-height, 60px));
                background-color: var(--primary-bg);
                border-right: 1px solid var(--primary-color);
                padding-top: 20px;
                transition: width 0.3s ease;
                overflow: hidden;
                z-index: 999;
            }

            .sidebar.collapsed { width: 60px; }

            .sidebar a {
                display: flex;
                align-items: center;
                padding: 12px 16px;
                color: var(--text-color);
                text-decoration: none;
                font-weight: 500;
            }

            .sidebar a i { width: 25px; text-align: center; margin-right: 10px; }
            .sidebar.collapsed a span { display: none; }
            .sidebar a:hover { background-color: var(--secondary-color); color: var(--text-on-primary); }

            .main-content {
                margin-top: var(--navbar-height, 60px);
                margin-left: var(--sidebar-width, 200px);
                padding: 30px;
                transition: margin-left 0.3s;
                flex: 1;
            }

            .main-content.expanded { margin-left: 60px; }

            .card, .table-section, .filters {
                background-color: #fff;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                margin-bottom: 20px;
            }

            table th {
                background: var(--primary-color);
                color: var(--text-on-primary);
                text-align: center;
            }

            .btn-primary, .btn-blue {
                background-color: var(--primary-color);
                color: var(--text-on-primary);
                border: none;
            }

            .btn-primary:hover, .btn-blue:hover {
                background-color: var(--secondary-color);
            }

            .form-control { border-radius: 0.5rem; }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <div class="d-flex align-items-center">
                <button id="toggleSidebar">☰</button>
                <img src="../<?php echo theme_logo(); ?>" alt="Logo" style="height:40px; margin-left:10px;">
            </div>
            <div class="user-info text-white">
                <?php echo $_SESSION['user']['username'] ?? ''; ?>
            </div>
        </nav>
    <?php
    return ob_get_clean();
}

function page_footer()
{
    ob_start();
    ?>
        <script>
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>
