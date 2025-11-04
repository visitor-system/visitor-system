<?php
session_start();

// Get user role from session
$role = $_SESSION['role'] ?? 'guest'; // fallback if role not set

// If user confirmed logout, destroy session
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_destroy();
    header("Location: login.php?message=logout_success");
    exit();
}

// Set the correct dashboard path for each role
switch ($role) {
    case 'admin':
        $dashboard = '../admin/dashboard.php'; // put actual admin path
        break;
    case 'host':
        $dashboard = '../Host/dashboard.php'; // put actual host path
        break;
    case 'security':
        $dashboard = '../security/dashboard.php'; // put actual security path
        break;
    default:
        $dashboard = '/login.php'; // fallback if role unknown
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <script>
        function confirmLogout() {
            let confirmAction = confirm("Are you sure you want to logout?");
            if (confirmAction) {
                // Redirect with confirmation
                window.location.href = "logout.php?confirm=yes";
            } else {
                // Redirect back to the correct dashboard
                window.location.href = "<?php echo $dashboard; ?>";
            }
        }

        // Run confirmation on page load
        window.onload = confirmLogout;
    </script>
</head>

<body>
</body>

</html>