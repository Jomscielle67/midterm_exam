<?php
$host = 'localhost';
$db = 'db_test_test';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query para siguradong gagawa ng `users` table
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE,
        is_verified TINYINT(1) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// SQL query para gumawa ng `requests` table
$conn->query("
    CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        request_type VARCHAR(100) NOT NULL,
        student_id VARCHAR(20) NOT NULL,
        course VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

// Check kung may admin account na
$check_admin_query = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin_query);

if ($result->num_rows === 0) {
    $hashed_password = password_hash('1234', PASSWORD_DEFAULT);
    $admin_query = "INSERT INTO users (username, password, email, is_verified, status) 
                    VALUES ('admin', '$hashed_password', 'admin@example.com', 1, 'admin')";
    $conn->query($admin_query);
}
?>
