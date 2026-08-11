<?php
require_once "../includes/auth_admin.php";
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /bidboard/admin/reports.php");
    exit();
}

if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {
    die("Invalid CSRF token.");
}

$report_id = (int) ($_POST["report_id"] ?? 0);
$status = $_POST["status"] ?? "";

if ($report_id <= 0 || !in_array($status, ["reviewed", "dismissed"])) {
    header("Location: /bidboard/admin/reports.php");
    exit();
}

$stmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $report_id);
$stmt->execute();
$stmt->close();

$_SESSION["flash"] = "Report marked as " . $status . ".";
header("Location: /bidboard/admin/reports.php");
exit();
