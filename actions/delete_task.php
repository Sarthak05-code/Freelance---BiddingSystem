<?php
// Action: delete a task (client can only delete their own open tasks)
// Cascade delete on the DB will remove associated bids automatically

session_name('bidboard_client');
session_start();

if (!isset($_SESSION['client_id'])) {
    header('Location: /bidboard/auth/client_login.php');
    exit();
}

require_once '../includes/db.php';
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token. Please go back and try again.');
}

$client_id = $_SESSION['client_id'];
$task_id   = (int)($_POST['task_id']  ?? 0);
$redirect  = trim($_POST['redirect'] ?? 'dashboard');

if ($task_id <= 0) {
    header('Location: /bidboard/client/dashboard.php');
    exit();
}

// Only allow deleting tasks that belong to this client and are still open
$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND client_id = ? AND status = 'open'");
$stmt->bind_param('ii', $task_id, $client_id);
$stmt->execute();
$stmt->close();

$_SESSION['flash'] = 'Task deleted.';

header('Location: /bidboard/client/dashboard.php');
exit();
