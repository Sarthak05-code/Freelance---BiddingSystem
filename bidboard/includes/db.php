<?php
// Database credentials — change if your XAMPP MySQL uses a different password
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');               // XAMPP default is empty password
define('DB_NAME', 'bidboard');

// Create a MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop everything if connection fails
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for full unicode support
$conn->set_charset('utf8mb4');
?>
