<?php
require 'includes/db.php';

// Create company_themes table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS company_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    primary_color VARCHAR(20) DEFAULT '#007bff',
    secondary_color VARCHAR(20) DEFAULT '#0056b3',
    logo_path VARCHAR(255),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Company themes table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// Create directory for company logos if it doesn't exist
$themesDir = __DIR__ . '/assets/company_themes';
if (!file_exists($themesDir)) {
    mkdir($themesDir, 0777, true);
    echo "\nCreated company_themes directory";
}

$conn->close();
?>