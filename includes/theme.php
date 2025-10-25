<?php
// theme.php - Load system theme and expose variables

require_once __DIR__ . '/db.php'; // Correct path

// Default theme
$theme = [
    'primary_color' => '#2563eb',
    'secondary_color' => '#1d4ed8',
    'logo_path' => 'assets/default-logo.svg'
];

// Load from system_theme table if exists
if ($conn) {
    $res = $conn->query("SELECT primary_color, secondary_color, logo_path FROM system_theme LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['primary_color'])) $theme['primary_color'] = $row['primary_color'];
        if (!empty($row['secondary_color'])) $theme['secondary_color'] = $row['secondary_color'];
        if (!empty($row['logo_path'])) $theme['logo_path'] = $row['logo_path'];
    }
}

// Make theme available in templates
function theme_css_vars() {
    global $theme;

    $primary = htmlspecialchars($theme['primary_color']);
    $secondary = htmlspecialchars($theme['secondary_color']);

    // Calculate text color based on primary background
    $rgb = sscanf($primary, "#%02x%02x%02x");
    $luminance = (0.299*$rgb[0] + 0.587*$rgb[1] + 0.114*$rgb[2])/255;
    $text_on_primary = $luminance < 0.5 ? '#ffffff' : '#2c3e50';

    echo "<style>
        :root {
            --primary-color: {$primary} !important;
            --secondary-color: {$secondary} !important;
            --text-on-primary: {$text_on_primary} !important;
            --sidebar-width: 200px !important;
            --navbar-height: 60px !important;
        }

        body {
            font-family: 'Segoe UI', sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #f4f6f8 !important;
            color: #2c3e50 !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: var(--navbar-height) !important;
            background: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 1.5rem !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            z-index: 1040 !important;
        }

        .navbar .logo-container img {
            height: 45px !important;
            object-fit: contain !important;
        }

        .sidebar {
            position: fixed !important;
            top: var(--navbar-height) !important;
            left: 0 !important;
            width: var(--sidebar-width) !important;
            height: calc(100vh - var(--navbar-height)) !important;
            background: #fff !important;
            border-right: 1px solid rgba(0,0,0,0.1) !important;
            padding-top: 1rem !important;
            overflow-y: auto !important;
            transition: all 0.3s ease !important;
            z-index: 1030 !important;
        }

        .sidebar a {
            display: flex !important;
            align-items: center !important;
            padding: 0.75rem 1.5rem !important;
            color: #2c3e50 !important;
            text-decoration: none !important;
            border-radius: 0.5rem !important;
            margin: 0.25rem 0.5rem !important;
            font-weight: 500 !important;
            transition: all 0.2s !important;
        }

        .sidebar a:hover {
            background: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
        }

        .main-content {
            margin-left: var(--sidebar-width) !important;
            margin-top: var(--navbar-height) !important;
            padding: 2rem !important;
            transition: margin-left 0.3s ease !important;
        }

        .main-content.expanded {
            margin-left: 0 !important;
        }

        .card {
            background: #fff !important;
            border-radius: 1rem !important;
            padding: 1.5rem !important;
            margin-bottom: 1.5rem !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        table th {
            background: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
            padding: 1rem !important;
            text-align: left !important;
            font-weight: 600 !important;
        }

        table td {
            padding: 1rem !important;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }

        .btn-primary {
            background: var(--primary-color) !important;
            color: var(--text-on-primary) !important;
            border: none !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            transition: all 0.2s !important;
        }

        .btn-primary:hover {
            background: var(--secondary-color) !important;
        }

        .form-control {
            border: 1px solid rgba(0,0,0,0.1) !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s !important;
        }

        .form-control:focus {
            border-color: var(--primary-color) !important;
        }

        @media (max-width:768px) {
            .sidebar { transform: translateX(-100%) !important; }
            .sidebar.show { transform: translateX(0) !important; }
            .main-content { margin-left: 0 !important; padding: 1rem !important; }
        }
    </style>";
}

function theme_logo() {
    global $theme;
    return $theme['logo_path'];
}
?>
