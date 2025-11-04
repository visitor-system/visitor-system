<?php
session_start();
require '../includes/db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if ($email === '' || $password === '') {
    header("Location: login.php?error=invalid");
    exit;
}

// Fetch user by email
$stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Validate user
if (!$user || strtolower($user['status']) !== 'active') {
    header("Location: login.php?error=invalid");
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    header("Location: login.php?error=invalid");
    exit;
}

// Get role from DB
$role = strtolower($user['role'] ?? '');

// Set session
$_SESSION['user'] = $user;
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $role;

// Redirect based on role
switch ($role) {
    case 'admin':
        header("Location: ../admin/dashboard.php");
        break;
    case 'host':
        header("Location: ../Host/dashboard.php");
        break;
    case 'security':
        header("Location: ../security/dashboard.php");
        break;
    default:
        header("Location: login.php?error=invalid");
}
exit;
?>