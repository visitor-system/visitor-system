<?php
session_start();
require '../includes/db.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'security') {
  header("Location: login.html");
  exit;
}

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
  die("Invalid pass ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $material = $conn->real_escape_string($_POST['material'] ?? '');
  $now = date('Y-m-d H:i:s');

  $update = $conn->query("UPDATE passes SET status='inside', checkin_time='$now', material='$material' WHERE id='$id'");

  if ($update) {
    header("Location: track_visitors.php");
    exit;
  } else {
    echo "<div class='alert alert-danger'>Error updating status: " . $conn->error . "</div>";
  }
}

$visitor = $conn->query("SELECT passes.pass_number, appointments.visitor_name 
                         FROM passes 
                         JOIN appointments ON passes.appointment_id = appointments.id 
                         WHERE passes.id='$id'")->fetch_assoc();

if (!$visitor || !isset($visitor['visitor_name'], $visitor['pass_number'])) {
  die("Visitor not found or data incomplete.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Check-in Visitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', sans-serif;
      color: #2c3e50;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-card {
      background-color: #ffffff;
      border-radius: 12px;
      padding: 30px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h3 {
      text-align: center;
      margin-bottom: 25px;
      font-weight: 600;
      color: #34495e;
    }

    .form-label {
      font-weight: 500;
      margin-bottom: 6px;
    }

    .form-control {
      border-radius: 8px;
      background-color: #f0f3f7;
      border: 1px solid #ccc;
      color: #2c3e50;
      padding: 10px;
      font-size: 15px;
    }

    .form-control::placeholder {
      color: #999;
    }

    .btn-blue {
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .btn-blue:hover {
      background-color: #0056b3;
    }

    .btn-outline {
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
    }

    @media (max-width: 576px) {
      .form-card {
        padding: 20px;
      }
    }
  </style>
</head>

<body>

  <div class="form-card">
    <h3>✅ Check-in Visitor</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Visitor Name</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($visitor['visitor_name']) ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Pass Number</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($visitor['pass_number']) ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Material Carried</label>
        <input type="text" name="material" class="form-control" placeholder="e.g. Laptop, Documents">
      </div>
      <div class="d-flex justify-content-between mt-4">
        <a href="track_visitors.php" class="btn-outline">← Cancel</a>
        <button type="submit" class="btn-blue">Confirm Check-in</button>
      </div>
    </form>
  </div>

</body>

</html>