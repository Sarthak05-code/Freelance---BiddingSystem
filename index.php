<?php
// Public homepage — lists all open tasks, no login required

require_once 'includes/db.php';

// Get filter values from URL query string
$search   = trim($_GET['search']   ?? '');   // keyword search
$category = trim($_GET['category'] ?? '');   // filter by category

// Build query dynamically based on active filters
$sql    = "SELECT t.*, c.name AS client_name,
                  (SELECT COUNT(*) FROM bids b WHERE b.task_id = t.id) AS bid_count
           FROM tasks t
           JOIN clients c ON t.client_id = c.id
           WHERE t.status = 'open'";   // only show open tasks publicly

$params = [];    // values for prepared statement
$types  = '';    // type string for bind_param

// Add keyword search if provided
if ($search !== '') {
    $sql     .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

// Add category filter if provided
if ($category !== '') {
    $sql     .= " AND t.category = ?";
    $params[] = $category;
    $types   .= 's';
}

$sql .= " ORDER BY t.created_at DESC";   // newest tasks first

// Execute query
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);   // spread array into bind_param
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);   // fetch all as array
$stmt->close();

// Get distinct categories for the filter dropdown
$cats_result = $conn->query("SELECT DISTINCT category FROM tasks WHERE status = 'open' ORDER BY category");
$categories  = $cats_result->fetch_all(MYSQLI_ASSOC);

$page_title  = 'Browse Tasks';
$nav_context = 'public';
require_once 'includes/header.php';
?>

<div class="page-wrap">
    <div class="container">

        <!-- Page heading -->
        <div class="page-header">
            <h1>Open Tasks</h1>
            <p>Browse available projects and submit your bid — no account needed.</p>
        </div>

        <!-- Search and filter bar -->
        <form method="GET" action="" style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <!-- Keyword search input -->
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search tasks..."
                value="<?= htmlspecialchars($search) ?>"
                style="flex:1; min-width:200px;"
            >

            <!-- Category dropdown filter -->
            <select name="category" class="form-control" style="width:200px;">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option
                        value="<?= htmlspecialchars($cat['category']) ?>"
                        <?= $category === $cat['category'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>

            <!-- Clear filters link -->
            <?php if ($search || $category): ?>
                <a href="/bidboard/index.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Task grid or empty state -->
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <h3>No tasks found</h3>
                <p>Try a different search or check back later.</p>
            </div>
        <?php else: ?>
            <div class="task-grid">
                <?php foreach ($tasks as $task): ?>
                    <!-- Each task is a clickable card -->
                    <a href="/bidboard/task.php?id=<?= $task['id'] ?>" class="task-card">
                        <div class="task-card-title"><?= htmlspecialchars($task['title']) ?></div>
                        <div class="task-card-desc"><?= htmlspecialchars($task['description']) ?></div>

                        <div class="task-card-meta">
                            <!-- Category badge -->
                            <span class="badge badge-category"><?= htmlspecialchars($task['category']) ?></span>

                            <!-- Budget display -->
                            <span class="text-sm" style="color:var(--success); font-weight:600;">
                                $<?= number_format($task['budget'], 2) ?>
                            </span>

                            <!-- Bid count -->
                            <span class="text-sm text-muted">
                                <?= $task['bid_count'] ?> bid<?= $task['bid_count'] != 1 ? 's' : '' ?>
                            </span>

                            <!-- Deadline -->
                            <span class="text-sm text-muted" style="margin-left:auto;">
                                Due <?= date('M j', strtotime($task['deadline'])) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
