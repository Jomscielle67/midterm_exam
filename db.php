<?php
$host = 'localhost';
$db = 'exam_test';
$user = 'root';
$pass = '';

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the 'users' table exists with created_at column
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE,
        is_verified TINYINT(1) DEFAULT 1,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Check if admin account exists
$check_admin_query = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin_query);

if ($result->num_rows === 0) {
    $hashed_password = password_hash('1234', PASSWORD_DEFAULT);
    $admin_query = "INSERT INTO users (username, password, email, is_verified, status) 
                    VALUES ('admin', '$hashed_password', 'admin@example.com', 1, 'admin')";
    $conn->query($admin_query);
}
?>
