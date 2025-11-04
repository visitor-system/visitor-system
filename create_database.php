<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "visitor_pass";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}
$conn->select_db($dbname);

// USERS TABLE
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    contact VARCHAR(15),
    role ENUM('admin','security','host') DEFAULT NULL,
    password VARCHAR(60) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    department VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
$conn->query($sql);

// DEPARTMENTS TABLE
$sql = "
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
$conn->query($sql);

// COMPANIES TABLE
$sql = "
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    location VARCHAR(30) NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
$conn->query($sql);

// APPOINTMENTS TABLE
$sql = "
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(30) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    company VARCHAR(30) NOT NULL,
    host_id INT NOT NULL,
    purpose TEXT NOT NULL,
    appointment_time DATETIME NOT NULL,
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
";
$conn->query($sql);

// PASSES TABLE
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
    time_spent VARCHAR(30),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
";
$conn->query($sql);



echo "Database and tables created successfully with optimized field lengths!";
?>