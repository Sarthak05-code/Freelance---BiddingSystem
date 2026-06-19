<?php
// Action: update task status (used for marking a task 'completed')
// Only allows valid forward transitions: in_progress → completed

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
$new_status = trim($_POST['status']   ?? '');
$redirect   = trim($_POST['redirect'] ?? 'dashboard');   // where to send after

// Only 'completed' is allowed from this form
$allowed = ['completed'];

if ($task_id <= 0 || !in_array($new_status, $allowed)) {
    header('Location: /bidboard/client/dashboard.php');
    exit();
}

// Verify task belongs to this client and is currently in_progress
$check = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND client_id = ? AND status = 'in_progress'");
$check->bind_param('ii', $task_id, $client_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $check->close();
    header('Location: /bidboard/client/dashboard.php');
    exit();
}
$check->close();

// Update task status
$stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
$stmt->bind_param('si', $new_status, $task_id);
$stmt->execute();
$stmt->close();

$_SESSION['flash'] = 'Task marked as completed.';

// Redirect back to wherever the action was triggered from
if ($redirect === 'bids') {
    header('Location: /bidboard/client/task_bids.php?id=' . $task_id);
} else {
    header('Location: /bidboard/client/dashboard.php');
}
exit();
