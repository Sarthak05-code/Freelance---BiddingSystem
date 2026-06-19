<?php
// Action: reject a single bid

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
$bid_id    = (int)($_POST['bid_id']  ?? 0);
$task_id   = (int)($_POST['task_id'] ?? 0);

if ($bid_id <= 0 || $task_id <= 0) {
    header('Location: /bidboard/client/dashboard.php');
    exit();
}

// Verify task belongs to this client
$check = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND client_id = ?");
$check->bind_param('ii', $task_id, $client_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $check->close();
    header('Location: /bidboard/client/dashboard.php');
    exit();
}
$check->close();

// Update only that bid's status to rejected
$stmt = $conn->prepare("UPDATE bids SET status = 'rejected' WHERE id = ? AND task_id = ?");
$stmt->bind_param('ii', $bid_id, $task_id);
$stmt->execute();
$stmt->close();

$_SESSION['flash'] = 'Bid rejected.';

header('Location: /bidboard/client/task_bids.php?id=' . $task_id);
exit();
