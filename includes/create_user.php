<?php
// create_user.php
session_start();
require '../includes/db.php'; // DB connection

$users = [
    [
        'name' => 'Admin User',
        'username' => 'admin',
        'contact' => '9876543210',
        'email' => 'admin@example.com',
        'password' => 'admin123',
        'role' => 'admin',
        'department' => 'HR'
    ],
    [
        'name' => 'Security User',
        'username' => 'security',
        'contact' => '9876543211',
        'email' => 'security@example.com',
        'password' => 'security123',
        'role' => 'security',
        'department' => 'HR'
    ],
    [
        'name' => 'Host User',
        'username' => 'host',
        'contact' => '9876543212',
        'email' => 'host@example.com',
        'password' => 'host123',
        'role' => 'host',
        'department' => 'HR'
    ],
];

foreach ($users as $user) {
    $name = $user['name'];
    $username = $user['username'];
    $contact = $user['contact'];
    $email = $user['email'];
    $password = password_hash($user['password'], PASSWORD_DEFAULT); // PHP hash
    $role = $user['role'];
    $status = 'active';
    $department = $user['department'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "$role user already exists.<br>";
        continue;
    }
    $stmt->close();

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name,username,contact,email,password,role,status,department) VALUES(?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $name, $username, $contact, $email, $password, $role, $status, $department);
    if ($stmt->execute()) {
        echo "$role user created successfully! Email: $email | Password: {$user['password']}<br>";
    } else {
        echo "Error creating $role user: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$conn->close();
?>