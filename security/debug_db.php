<?php
// Debug script to check database connection and table structure
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Database Debug Information</h2>";

// Check connection
if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Check database
echo "<h3>Database: " . $conn->database . "</h3>";

// List all tables
echo "<h3>Available Tables:</h3>";
$tables = $conn->query("SHOW TABLES");
if ($tables) {
    echo "<ul>";
    while ($row = $tables->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Error listing tables: " . $conn->error . "</p>";
}

// Check specific tables structure
$required_tables = ['passes', 'appointments', 'users'];

foreach ($required_tables as $table) {
    echo "<h3>Table: $table</h3>";
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        if ($structure) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
    }
}

// Test the problematic query
echo "<h3>Testing Query</h3>";
$test_query = "SELECT p.*, a.visitor_name, a.company, a.purpose, a.appointment_time, a.mobile, a.whom_to_meet, u.username as host_name
              FROM passes p
              JOIN appointments a ON p.appointment_id = a.id
              LEFT JOIN users u ON a.host_id = u.id
              WHERE p.id = ?";

$stmt = $conn->prepare($test_query);
if (!$stmt) {
    echo "<p style='color: red;'>❌ Prepare failed: " . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Query prepared successfully</p>";
    $stmt->close();
}

$conn->close();
?>
