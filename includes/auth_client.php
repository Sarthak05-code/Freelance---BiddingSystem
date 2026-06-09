<?php
// Guard for client-only pages
// Include this at the top of every client page

// Unique session name — separate from admin session
session_name('bidboard_client');
session_start();

// If not logged in as client, redirect to client login
if (!isset($_SESSION['client_id'])) {
    header('Location: /bidboard/auth/client_login.php');
    exit();
}
?>
