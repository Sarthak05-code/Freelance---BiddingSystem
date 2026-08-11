<?php
// Logout handler — works for both admin and client
// Usage: logout.php?role=admin  OR  logout.php?role=client

$role = $_GET["role"] ?? "client";

if ($role === "admin") {
    session_name("bidboard_admin");
    session_start();
    unset($_SESSION["admin_id"]);
    unset($_SESSION["admin_name"]);
    session_write_close();
    header("Location: /bidboard/auth/admin_login.php");
} else {
    session_name("bidboard_client");
    session_start();
    unset($_SESSION["client_id"]);
    unset($_SESSION["client_name"]);
    session_write_close();
    header("Location: /bidboard/auth/client_login.php");
}

exit();
