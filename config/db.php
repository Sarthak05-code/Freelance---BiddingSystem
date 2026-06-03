<?php

// --- DATABASE CONFIGURATION ---
// Change these values to match your XAMPP MySQL setup
$host = 'localhost';   // XAMPP always runs MySQL on localhost
$db   = 'snippet_vault'; // the database name we created
$user = 'root';        // default XAMPP MySQL username
$pass = '';            // default XAMPP MySQL password is empty

try {
    // PDO (PHP Data Objects) — a safe way to connect to MySQL
    // It protects against SQL injection when used with prepared statements
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);

    // ERRMODE_EXCEPTION — if a query fails, PHP will throw an error
    // instead of silently failing (makes debugging easier)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // FETCH_ASSOC — when we fetch rows, return them as associative arrays
    // e.g. $row['title'] instead of $row[0]
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop the page and show the error
    die("Database connection failed: " . $e->getMessage());
}
