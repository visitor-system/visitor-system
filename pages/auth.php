<?php
session_start();
require '../includes/db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$loginType = strtolower($_POST['login_type'] ?? '');

if($email==='' || $password===''){ header("Location: login.html?error=invalid"); exit; }

$stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
$stmt->bind_param("s",$email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user){ header("Location: login.html?error=invalid"); exit; }
if(strtolower($user['status'])!=='active'){ header("Location: login.html?error=invalid"); exit; }
if(!password_verify($password,$user['password'])){ header("Location: login.html?error=invalid"); exit; }

$role = strtolower($user['role']);
if($loginType==='admin' && $role!=='admin' || $loginType==='security' && $role!=='security' || $loginType==='user' && $role!=='host'){
    header("Location: login.html?error=wrongtype"); exit;
}

$_SESSION['user']=$user;
$_SESSION['user_id']=$user['id'];
$_SESSION['role']=$role;

// Redirect
if($role==='admin') header("Location: ../admin/dashboard.php");
elseif($role==='security') header("Location: ../security/dashboard.php");
else header("Location:../Host/dashboard.php");
exit;
?>
