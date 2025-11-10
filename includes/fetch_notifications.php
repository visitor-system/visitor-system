<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// Fetch latest notifications
$res = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
$notifications = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($notifications);
