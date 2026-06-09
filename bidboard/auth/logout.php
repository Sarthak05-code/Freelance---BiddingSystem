<?php
// Logout handler — works for both admin and client
// Usage: logout.php?role=admin  OR  logout.php?role=client

$role = $_GET['role'] ?? 'client';   // get which role is logging out

if ($role === 'admin') {
    session_name('bidboard_admin');   // target the admin session
    session_start();
    session_unset();                  // clear all session variables
    session_destroy();                // destroy the session
    header('Location: /bidboard/auth/admin_login.php');
} else {
    session_name('bidboard_client');  // target the client session
    session_start();
    session_unset();
    session_destroy();
    header('Location: /bidboard/auth/client_login.php');
}

exit();
?>
