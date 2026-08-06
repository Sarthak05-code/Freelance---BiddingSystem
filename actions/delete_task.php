<?php
// Action: delete a task (client can only delete their own open tasks)
// Cascade delete on the DB will remove associated bids automatically

session_name("bidboard_client");
session_start();

if (!isset($_SESSION["client_id"])) {
    header("Location: /bidboard/auth/client_login.php");
    exit();
}

require_once "../includes/db.php";
if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {
    die("Invalid CSRF token. Please go back and try again.");
}

$client_id = $_SESSION["client_id"];
$task_id = (int) ($_POST["task_id"] ?? 0);

if ($task_id <= 0) {
    header("Location: /bidboard/client/dashboard.php");
    exit();
}

// Verify task belongs to this client and is still open
$check = $conn->prepare(
    "SELECT id
     FROM tasks
     WHERE id = ?
       AND client_id = ?
       AND status = 'open'",
);

$check->bind_param("ii", $task_id, $client_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $check->close();

    $_SESSION["flash"] = "That task can no longer be deleted.";

    header("Location: /bidboard/client/dashboard.php");
    exit();
}

$check->close();

// Delete task
$stmt = $conn->prepare(
    "DELETE FROM tasks
     WHERE id = ?",
);

$stmt->bind_param("i", $task_id);
$stmt->execute();
$stmt->close();

$_SESSION["flash"] = "Task deleted.";

header("Location: /bidboard/client/dashboard.php");
exit();
