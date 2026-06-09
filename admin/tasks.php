<?php
// Admin: view and manage all tasks across all clients

require_once '../includes/auth_admin.php';
require_once '../includes/db.php';

// Flash message (after delete)
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Handle delete POST from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id'])) {
    $del_id = (int)$_POST['delete_task_id'];
    $stmt   = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param('i', $del_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['flash'] = 'Task deleted.';
    header('Location: /bidboard/admin/tasks.php');
    exit();
}

// Filter by status
$status_filter = trim($_GET['status'] ?? '');
$allowed_statuses = ['open', 'in_progress', 'completed'];

$sql    = "SELECT t.*, c.name AS client_name,
                  (SELECT COUNT(*) FROM bids b WHERE b.task_id = t.id) AS bid_count
           FROM tasks t
           JOIN clients c ON t.client_id = c.id";
$params = [];
$types  = '';

if (in_array($status_filter, $allowed_statuses)) {
    $sql    .= " WHERE t.status = ?";
    $params[] = $status_filter;
    $types   .= 's';
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title  = 'All Tasks';
$nav_context = 'admin';
require_once '../includes/header.php';
?>

<div class="page-wrap">
    <div class="container">

        <div class="page-header">
            <h1>All Tasks</h1>
            <p>Manage tasks across all clients</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Status filter tabs -->
        <div style="display:flex; gap:0.4rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <a href="/bidboard/admin/tasks.php"
               class="btn btn-sm <?= $status_filter === '' ? 'btn-primary' : 'btn-ghost' ?>">All</a>
            <a href="/bidboard/admin/tasks.php?status=open"
               class="btn btn-sm <?= $status_filter === 'open' ? 'btn-primary' : 'btn-ghost' ?>">Open</a>
            <a href="/bidboard/admin/tasks.php?status=in_progress"
               class="btn btn-sm <?= $status_filter === 'in_progress' ? 'btn-primary' : 'btn-ghost' ?>">In Progress</a>
            <a href="/bidboard/admin/tasks.php?status=completed"
               class="btn btn-sm <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-ghost' ?>">Completed</a>
        </div>

        <div class="card">
            <?php if (empty($tasks)): ?>
                <div class="empty-state"><h3>No tasks found</h3></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Category</th>
                                <th>Budget</th>
                                <th>Deadline</th>
                                <th>Bids</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task):
                                $badges = [
                                    'open'        => ['badge-open',     'Open'],
                                    'in_progress' => ['badge-progress', 'In Progress'],
                                    'completed'   => ['badge-done',     'Completed'],
                                ];
                                [$bc, $bl] = $badges[$task['status']] ?? ['badge-pending', $task['status']];
                            ?>
                                <tr>
                                    <td class="text-sm text-muted"><?= $task['id'] ?></td>
                                    <td>
                                        <a href="/bidboard/task.php?id=<?= $task['id'] ?>"
                                           style="color:var(--accent); text-decoration:none; font-weight:600;">
                                            <?= htmlspecialchars($task['title']) ?>
                                        </a>
                                    </td>
                                    <td class="text-sm"><?= htmlspecialchars($task['client_name']) ?></td>
                                    <td><span class="badge badge-category"><?= htmlspecialchars($task['category']) ?></span></td>
                                    <td class="text-sm" style="color:var(--success);">$<?= number_format($task['budget'], 2) ?></td>
                                    <td class="text-sm"><?= date('M j, Y', strtotime($task['deadline'])) ?></td>
                                    <td class="text-sm"><?= $task['bid_count'] ?></td>
                                    <td><span class="badge <?= $bc ?>"><?= $bl ?></span></td>
                                    <td>
                                        <!-- Admin delete — works on any task regardless of status -->
                                        <form method="POST" action=""
                                              onsubmit="return confirm('Delete task #<?= $task['id'] ?> and all its bids?')">
                                            <input type="hidden" name="delete_task_id" value="<?= $task['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
