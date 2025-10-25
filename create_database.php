<?php
// database_setup.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "visitor_pass";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}
$conn->select_db($dbname);

// ======= USERS TABLE =======
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact VARCHAR(15),
    role ENUM('admin','security','host') DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
if (!$conn->query($sql)) {
    die("Error creating users table: " . $conn->error);
}

// ======= DEPARTMENTS TABLE =======
$sql = "
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
if (!$conn->query($sql)) {
    die("Error creating departments table: " . $conn->error);
}

// ======= COMPANIES TABLE =======
$sql = "
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
if (!$conn->query($sql)) {
    die("Error creating companies table: " . $conn->error);
}

// ======= APPOINTMENTS TABLE =======
$sql = "
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    company VARCHAR(100) NOT NULL,
    host_id INT NOT NULL,
    purpose TEXT NOT NULL,
    appointment_time DATETIME NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
";
if (!$conn->query($sql)) {
    die("Error creating appointments table: " . $conn->error);
}

// ======= PASSES TABLE =======
$sql = "
CREATE TABLE IF NOT EXISTS passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    pass_number VARCHAR(20) NOT NULL UNIQUE,
    qr_code TEXT,
    status ENUM('waiting','inside','out') DEFAULT 'waiting',
    checkin_time DATETIME,
    checkout_time DATETIME,
    material TEXT,
    time_spent VARCHAR(50),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
";
if (!$conn->query($sql)) {
    die("Error creating passes table: " . $conn->error);
}

// ======= SAMPLE DATA (OPTIONAL) =======
$conn->query("INSERT INTO users (name,email,contact,role,password) VALUES 
('Admin User','admin@example.com','1234567890','admin','" . password_hash('admin123', PASSWORD_DEFAULT) . "') 
ON DUPLICATE KEY UPDATE email=email;");

$conn->query("INSERT INTO companies (name,location) VALUES 
('ABC Pvt Ltd','Mumbai'), 
('XYZ Solutions','Pune') 
ON DUPLICATE KEY UPDATE name=name;");

$conn->query("INSERT INTO users (name,email,contact,role,password) VALUES 
('Host One','host1@example.com','9876543210','host','" . password_hash('host123', PASSWORD_DEFAULT) . "'), 
('Host Two','host2@example.com','8765432109','host','" . password_hash('host123', PASSWORD_DEFAULT) . "') 
ON DUPLICATE KEY UPDATE email=email;");

echo "Database and tables created successfully with sample users and companies!";
?>