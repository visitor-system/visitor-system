<?php
session_start();
require '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$visitor_name = $conn->real_escape_string($data['visitor_name']);
$mobile = $conn->real_escape_string($data['mobile']);
$company = $conn->real_escape_string($data['company']);
$appointment_time = $conn->real_escape_string($data['appointment_time']);

$sql = "UPDATE appointments 
        JOIN passes ON passes.appointment_id = appointments.id
        SET appointments.visitor_name='$visitor_name',
            appointments.mobile='$mobile',
            appointments.company='$company',
            appointments.appointment_time='$appointment_time'
        WHERE passes.id=$id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>