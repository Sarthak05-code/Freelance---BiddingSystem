<?php
// Public page — freelancer enters their email to check all their bids
// No account needed, just email lookup

require_once 'includes/db.php';

$email  = trim($_POST['email'] ?? '');   // submitted email
$bids   = [];                            // will hold results
$searched = false;                       // tracks if a search was made

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email !== '') {
    $searched = true;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        // Fetch all bids by this email with task details
        $stmt = $conn->prepare(
            "SELECT b.*, t.title AS task_title, t.id AS task_id,
                    t.budget AS task_budget, t.status AS task_status,
                    t.deadline AS task_deadline, c.name AS client_name
             FROM bids b
             JOIN tasks t   ON b.task_id    = t.id
             JOIN clients c ON t.client_id  = c.id
             WHERE b.freelancer_email = ?
             ORDER BY b.submitted_at DESC"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $bids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

$page_title  = 'My Bid History';
$nav_context = 'public';
require_once 'includes/header.php';
?>

<div class="page-wrap">
    <div class="container" style="max-width:700px;">

        <div class="page-header">
            <h1>Check your bids</h1>
            <p>Enter the email you used when bidding to see all your submissions.</p>
        </div>

        <!-- Email lookup form -->
        <form method="POST" action="" style="display:flex; gap:0.75rem; margin-bottom:2rem; flex-wrap:wrap;">
            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="you@example.com"
                value="<?= htmlspecialchars($email) ?>"
                style="flex:1; min-width:220px;"
                required>
            <button type="submit" class="btn btn-primary">Check bids</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>

        <?php elseif ($searched && empty($bids)): ?>
            <div class="empty-state">
                <h3>No bids found</h3>
                <p>No bids were submitted with <strong><?= htmlspecialchars($email) ?></strong>.</p>
                <p class="text-sm text-muted" style="margin-top:0.4rem;">
                    Double check your email or <a href="/bidboard/index.php" style="color:var(--accent);">browse open tasks</a>.
                </p>
            </div>

        <?php elseif (!empty($bids)): ?>
            <p class="text-sm text-muted" style="margin-bottom:1rem;">
                Found <strong><?= count($bids) ?></strong> bid<?= count($bids) != 1 ? 's' : '' ?> for
                <strong><?= htmlspecialchars($email) ?></strong>
            </p>

            <?php foreach ($bids as $bid):
                // Map bid status to badge + message
                $bid_badge = [
                    'pending'  => ['badge-pending',  'Pending',  'The client has not reviewed your bid yet.'],
                    'accepted' => ['badge-accepted', 'Accepted', 'Congratulations! The client accepted your bid.'],
                    'rejected' => ['badge-rejected', 'Rejected', 'The client went with a different bid.'],
                ];
                [$bc, $bl, $msg] = $bid_badge[$bid['status']] ?? ['badge-pending', 'Pending', ''];

                // Map task status to badge
                $task_badges = [
                    'open'        => ['badge-open',     'Open'],
                    'in_progress' => ['badge-progress', 'In Progress'],
                    'completed'   => ['badge-done',     'Completed'],
                ];
                [$tbc, $tbl] = $task_badges[$bid['task_status']] ?? ['badge-pending', $bid['task_status']];
            ?>
                <div class="card" style="margin-bottom:1rem;">
                    <div class="card-body">

                        <!-- Task title + status -->
                        <div class="flex items-center gap-1" style="flex-wrap:wrap; margin-bottom:0.5rem;">
                            <a href="/bidboard/task.php?id=<?= $bid['task_id'] ?>"
                                style="font-weight:700; font-size:1rem; color:var(--accent); text-decoration:none;">
                                <?= htmlspecialchars($bid['task_title']) ?>
                            </a>
                            <span class="badge <?= $tbc ?>"><?= $tbl ?></span>
                        </div>

                        <!-- Client + deadline -->
                        <p class="text-sm text-muted" style="margin-bottom:1rem;">
                            Posted by <strong><?= htmlspecialchars($bid['client_name']) ?></strong>
                            &nbsp;&middot;&nbsp;
                            Deadline: <?= date('M j, Y', strtotime($bid['task_deadline'])) ?>
                            &nbsp;&middot;&nbsp;
                            Task budget: $<?= number_format($bid['task_budget'], 2) ?>
                        </p>

                        <!-- Divider -->
                        <div style="border-top:1px solid var(--border); margin-bottom:1rem;"></div>

                        <!-- Bid details + status -->
                        <div class="flex items-center justify-between" style="flex-wrap:wrap; gap:1rem;">
                            <div>
                                <div class="flex items-center gap-1" style="margin-bottom:0.3rem;">
                                    <span class="text-sm font-bold">Your bid:</span>
                                    <span style="color:var(--success); font-weight:700;">
                                        $<?= number_format($bid['proposed_price'], 2) ?>
                                    </span>
                                    <span class="badge <?= $bc ?>"><?= $bl ?></span>
                                </div>
                                <!-- Status message -->
                                <p class="text-sm text-muted"><?= $msg ?></p>
                            </div>
                            <span class="text-sm text-muted">
                                Submitted <?= date('M j, Y', strtotime($bid['submitted_at'])) ?>
                            </span>
                        </div>

                        <!-- Show pitch -->
                        <div style="margin-top:1rem; padding:0.75rem 1rem;
                                    background:var(--bg); border-radius:var(--radius);
                                    font-size:0.88rem; line-height:1.6; color:var(--muted);">
                            <span class="font-bold" style="color:var(--text);">Your pitch: </span>
                            <?= nl2br(htmlspecialchars($bid['pitch'])) ?>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>