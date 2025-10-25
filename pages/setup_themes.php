<?php
require 'includes/db.php';

// Create company_themes table
$sql = "CREATE TABLE IF NOT EXISTS company_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    primary_color VARCHAR(20) DEFAULT '#007bff',
    secondary_color VARCHAR(20) DEFAULT '#0056b3',
    logo_path VARCHAR(255),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "company_themes table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create a themes folder if it doesn't exist
$themesDir = __DIR__ . '/assets/company_themes';
if (!file_exists($themesDir)) {
    mkdir($themesDir, 0777, true);
    echo "Created company_themes directory\n";
}

$conn->close();
?>