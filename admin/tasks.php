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

// Filter by status and keyword search
$status_filter    = trim($_GET['status'] ?? '');
$search           = trim($_GET['search'] ?? '');   // keyword from search box
$allowed_statuses = ['open', 'in_progress', 'completed'];

$sql    = "SELECT t.*, c.name AS client_name,
                  (SELECT COUNT(*) FROM bids b WHERE b.task_id = t.id) AS bid_count
           FROM tasks t
           JOIN clients c ON t.client_id = c.id
           WHERE 1=1";   // 1=1 so we can safely append AND clauses
$params = [];
$types  = '';

// Apply status filter if valid
if (in_array($status_filter, $allowed_statuses)) {
    $sql    .= " AND t.status = ?";
    $params[] = $status_filter;
    $types   .= 's';
}

// Apply keyword search across title, description, and client name
if ($search !== '') {
    $like     = '%' . $search . '%';
    $sql     .= " AND (t.title LIKE ? OR t.description LIKE ? OR c.name LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
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

        <!-- Search bar — keyword search across title, description, client name -->
        <form method="GET" action="" style="display:flex; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
            <!-- Preserve status filter when searching -->
            <?php if ($status_filter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <?php endif; ?>
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search by title, description, or client name..."
                value="<?= htmlspecialchars($search) ?>"
                style="flex:1; min-width:220px;"
            >
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <!-- Clear search, preserve current status filter -->
                <a href="/bidboard/admin/tasks.php<?= $status_filter ? '?status=' . urlencode($status_filter) : '' ?>"
                   class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Status filter tabs — preserve search when switching tabs -->
        <div style="display:flex; gap:0.4rem; margin-bottom:1.25rem; flex-wrap:wrap;">
            <?php $sq = $search ? '&search=' . urlencode($search) : ''; ?>
            <a href="/bidboard/admin/tasks.php<?= $search ? '?search=' . urlencode($search) : '' ?>"
               class="btn btn-sm <?= $status_filter === '' ? 'btn-primary' : 'btn-ghost' ?>">All</a>
            <a href="/bidboard/admin/tasks.php?status=open<?= $sq ?>"
               class="btn btn-sm <?= $status_filter === 'open' ? 'btn-primary' : 'btn-ghost' ?>">Open</a>
            <a href="/bidboard/admin/tasks.php?status=in_progress<?= $sq ?>"
               class="btn btn-sm <?= $status_filter === 'in_progress' ? 'btn-primary' : 'btn-ghost' ?>">In Progress</a>
            <a href="/bidboard/admin/tasks.php?status=completed<?= $sq ?>"
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
