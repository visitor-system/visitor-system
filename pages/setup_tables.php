<?php
require '../includes/db.php';

// Create system theme table
$create_themes = "CREATE TABLE IF NOT EXISTS system_theme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    primary_color VARCHAR(20) DEFAULT '#007bff',
    secondary_color VARCHAR(20) DEFAULT '#0056b3',
    logo_path VARCHAR(255)
)";

if ($conn->query($create_themes)) {
    echo "System theme table created successfully\n";

    // Insert default theme if not exists
    $check_theme = "SELECT id FROM system_theme LIMIT 1";
    $result = $conn->query($check_theme);

    if ($result->num_rows == 0) {
        $insert_theme = "INSERT INTO system_theme (primary_color, secondary_color) VALUES ('#007bff', '#0056b3')";
        if ($conn->query($insert_theme)) {
            echo "Default theme added\n";
        } else {
            echo "Error adding default theme: " . $conn->error . "\n";
        }
    }
} else {
    echo "Error creating theme table: " . $conn->error . "\n";
}

// Now create company_themes table
$create_themes = "CREATE TABLE IF NOT EXISTS company_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    primary_color VARCHAR(20) DEFAULT '#007bff',
    secondary_color VARCHAR(20) DEFAULT '#0056b3',
    logo_path VARCHAR(255),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
)";

if ($conn->query($create_themes)) {
    echo "Company themes table created successfully\n";
} else {
    echo "Error creating themes table: " . $conn->error . "\n";
}

// Create themes directory if it doesn't exist
$themesDir = __DIR__ . '/assets/company_themes';
if (!file_exists($themesDir)) {
    if (mkdir($themesDir, 0777, true)) {
        echo "Created company_themes directory\n";
    } else {
        echo "Error creating company_themes directory\n";
    }
}

$conn->close();
?>