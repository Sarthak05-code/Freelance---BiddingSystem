<?php
// Guard for admin-only pages
// Include this at the top of every admin page

// Use a unique session name to avoid conflicts with client session
session_name('bidboard_admin');
session_start();

// If not logged in as admin, redirect to admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /bidboard/auth/admin_login.php');
    exit();
}
?>
