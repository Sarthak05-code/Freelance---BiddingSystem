<?php
// Single task page — shows task details and bid submission form
// Accessible to anyone (no login required)

require_once 'includes/db.php';

// Get task ID from URL
$task_id = (int)($_GET['id'] ?? 0);   // cast to int for safety

if ($task_id <= 0) {
    // Invalid ID — redirect home
    header('Location: /bidboard/index.php');
    exit();
}

// Fetch task with client name
$stmt = $conn->prepare(
    "SELECT t.*, c.name AS client_name
     FROM tasks t
     JOIN clients c ON t.client_id = c.id
     WHERE t.id = ?"
);
$stmt->bind_param('i', $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Task not found
if (!$task) {
    header('Location: /bidboard/index.php');
    exit();
}

$error   = '';
$success = '';

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $task['status'] === 'open') {
    $name     = trim($_POST['freelancer_name']  ?? '');
    $email    = trim($_POST['freelancer_email'] ?? '');
    $price    = trim($_POST['proposed_price']   ?? '');
    $pitch    = trim($_POST['pitch']            ?? '');

    // Validation
    if ($name === '' || $email === '' || $price === '' || $pitch === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Enter a valid bid amount.';
    } else {
        // Insert the bid
        $ins = $conn->prepare(
            "INSERT INTO bids (task_id, freelancer_name, freelancer_email, proposed_price, pitch)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->bind_param('issds', $task_id, $name, $email, $price, $pitch);

        if ($ins->execute()) {
            $success = 'Your bid was submitted successfully! The client will review it.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
        $ins->close();
    }
}

// Fetch existing bids for this task (public: show count and names only)
$bids_stmt = $conn->prepare(
    "SELECT freelancer_name, proposed_price, submitted_at, status
     FROM bids WHERE task_id = ? ORDER BY submitted_at DESC"
);
$bids_stmt->bind_param('i', $task_id);
$bids_stmt->execute();
$bids = $bids_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bids_stmt->close();

$page_title  = htmlspecialchars($task['title']);
$nav_context = 'public';
require_once 'includes/header.php';
?>

<div class="page-wrap">
    <div class="container">

        <!-- Back link -->
        <a href="/bidboard/index.php" class="text-sm" style="color:var(--muted); text-decoration:none; display:inline-block; margin-bottom:1rem;">
            &larr; Back to tasks
        </a>

        <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;">

            <!-- Left: task details -->
            <div>
                <div class="card">
                    <div class="card-body">
                        <!-- Title and status badge -->
                        <div class="flex items-center gap-1" style="flex-wrap:wrap; margin-bottom:0.5rem;">
                            <h1 style="font-size:1.4rem; font-weight:700; letter-spacing:-0.02em;">
                                <?= htmlspecialchars($task['title']) ?>
                            </h1>
                            <?php
                            // Map status to badge class
                            $status_badges = [
                                'open'        => 'badge-open',
                                'in_progress' => 'badge-progress',
                                'completed'   => 'badge-done',
                            ];
                            $badge_class = $status_badges[$task['status']] ?? 'badge-pending';
                            $status_labels = [
                                'open'        => 'Open',
                                'in_progress' => 'In Progress',
                                'completed'   => 'Completed',
                            ];
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?= $status_labels[$task['status']] ?? $task['status'] ?>
                            </span>
                        </div>

                        <!-- Meta row: client, category, budget, deadline -->
                        <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
                            <span class="text-sm text-muted">
                                Posted by <strong><?= htmlspecialchars($task['client_name']) ?></strong>
                            </span>
                            <span class="badge badge-category"><?= htmlspecialchars($task['category']) ?></span>
                            <span class="text-sm" style="color:var(--success); font-weight:600;">
                                Budget: $<?= number_format($task['budget'], 2) ?>
                            </span>
                            <span class="text-sm text-muted">
                                Deadline: <?= date('M j, Y', strtotime($task['deadline'])) ?>
                            </span>
                        </div>

                        <!-- Full task description -->
                        <div style="line-height:1.7; color:var(--text);">
                            <?= nl2br(htmlspecialchars($task['description'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Existing bids section (public view) -->
                <?php if (!empty($bids)): ?>
                    <div style="margin-top:1.25rem;">
                        <h3 style="font-size:0.95rem; font-weight:600; margin-bottom:0.75rem;">
                            <?= count($bids) ?> bid<?= count($bids) != 1 ? 's' : '' ?> submitted
                        </h3>
                        <?php foreach ($bids as $bid): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center;
                                        padding:0.75rem 1rem; background:var(--surface);
                                        border:1px solid var(--border); border-radius:var(--radius);
                                        margin-bottom:0.5rem;">
                                <div>
                                    <span class="font-bold text-sm"><?= htmlspecialchars($bid['freelancer_name']) ?></span>
                                    <span class="text-sm text-muted" style="margin-left:0.5rem;">
                                        $<?= number_format($bid['proposed_price'], 2) ?>
                                    </span>
                                </div>
                                <span class="text-sm text-muted">
                                    <?= date('M j', strtotime($bid['submitted_at'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: bid submission form -->
            <div>
                <?php if ($task['status'] !== 'open'): ?>
                    <!-- Task no longer accepting bids -->
                    <div class="card">
                        <div class="card-body" style="text-align:center; padding:2rem;">
                            <p class="text-muted">This task is no longer accepting bids.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">Submit your bid</div>
                        <div class="card-body">
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>
                            <?php if ($error): ?>
                                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <?php if (!$success): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label class="form-label" for="freelancer_name">Your name</label>
                                        <input
                                            type="text"
                                            id="freelancer_name"
                                            name="freelancer_name"
                                            class="form-control"
                                            placeholder="Jane Doe"
                                            value="<?= htmlspecialchars($_POST['freelancer_name'] ?? '') ?>"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="freelancer_email">Email</label>
                                        <input
                                            type="email"
                                            id="freelancer_email"
                                            name="freelancer_email"
                                            class="form-control"
                                            placeholder="you@example.com"
                                            value="<?= htmlspecialchars($_POST['freelancer_email'] ?? '') ?>"
                                            required
                                        >
                                        <p class="form-hint">The client will contact you here.</p>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="proposed_price">Your bid ($)</label>
                                        <input
                                            type="number"
                                            id="proposed_price"
                                            name="proposed_price"
                                            class="form-control"
                                            placeholder="e.g. 150"
                                            min="1"
                                            step="0.01"
                                            value="<?= htmlspecialchars($_POST['proposed_price'] ?? '') ?>"
                                            required
                                        >
                                        <p class="form-hint">Client budget: $<?= number_format($task['budget'], 2) ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="pitch">Why you?</label>
                                        <textarea
                                            id="pitch"
                                            name="pitch"
                                            class="form-control"
                                            placeholder="Briefly explain your experience and approach..."
                                            required
                                        ><?= htmlspecialchars($_POST['pitch'] ?? '') ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="width:100%;">
                                        Submit bid
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
