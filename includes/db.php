<?php
// Database credentials — change if your XAMPP MySQL uses a different password
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", ""); // XAMPP default is empty password
define("DB_NAME", "bidboard");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Start a session if one isn't already active (needed for CSRF tokens)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create a MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop everything if connection fails
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for full unicode support
$conn->set_charset("utf8mb4");

function generate_csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf_token($token)
{
    return isset($_SESSION["csrf_token"]) &&
        hash_equals($_SESSION["csrf_token"], $token ?? "");
}

?>
