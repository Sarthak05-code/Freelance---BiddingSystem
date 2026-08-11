<?php
require_once "../includes/auth_admin.php";
require_once "../includes/db.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

$reports = $conn
    ->query(
        "
    SELECT r.*, t.title AS task_title, b.freelancer_name AS bid_name
    FROM reports r
    LEFT JOIN tasks t ON r.task_id = t.id
    LEFT JOIN bids b ON r.bid_id = b.id
    ORDER BY r.status = 'pending' DESC, r.submitted_at DESC
",
    )
    ->fetch_all(MYSQLI_ASSOC);

$page_title = "Reports";
$nav_context = "admin";
require_once "../includes/header.php";
?>

<div class="page-wrap">
    <div class="container">
        <div class="page-header">
            <h1>Reports</h1>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
                $flash,
            ) ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($reports)): ?>
                <div class="empty-state"><h3>No reports</h3></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Target</th>
                                <th>Reporter</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r):
                                $status_class =
                                    [
                                        "pending" => "badge-pending",
                                        "reviewed" => "badge-accepted",
                                        "dismissed" => "badge-rejected",
                                    ][$r["status"]] ?? "badge-pending"; ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $r[
                                            "report_type"
                                        ] === "task"
                                            ? "badge-open"
                                            : "badge-progress" ?>">
                                            <?= ucfirst($r["report_type"]) ?>
                                        </span>
                                    </td>
                                    <td class="text-sm">
                                        <?php if (
                                            $r["report_type"] === "task"
                                        ): ?>
                                            <a href="/bidboard/task.php?id=<?= $r[
                                                "task_id"
                                            ] ?>" style="color:var(--accent); font-weight:600;">
                                                <?= htmlspecialchars(
                                                    $r["task_title"] ??
                                                        "Task #" .
                                                            $r["task_id"],
                                                ) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="font-bold"><?= htmlspecialchars(
                                                $r["bid_name"] ??
                                                    "Bid #" . $r["bid_id"],
                                            ) ?></span>
                                            <br>
                                            <a href="/bidboard/task.php?id=<?= $r[
                                                "task_id"
                                            ] ?>" class="text-sm text-muted" style="color:var(--accent);">View task</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-sm">
                                        <?= htmlspecialchars(
                                            $r["reporter_name"],
                                        ) ?>
                                        <br>
                                        <span class="text-muted"><?= htmlspecialchars(
                                            $r["reporter_email"],
                                        ) ?></span>
                                    </td>
                                    <td class="text-sm" style="max-width:250px;"><?= nl2br(
                                        htmlspecialchars($r["reason"]),
                                    ) ?></td>
                                    <td><span class="badge <?= $status_class ?>"><?= ucfirst(
    $r["status"],
) ?></span></td>
                                    <td>
                                        <?php if (
                                            $r["status"] === "pending"
                                        ): ?>
                                            <form method="POST" action="/bidboard/actions/update_report_status.php" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                                    generate_csrf_token(),
                                                ) ?>">
                                                <input type="hidden" name="report_id" value="<?= $r[
                                                    "id"
                                                ] ?>">
                                                <input type="hidden" name="status" value="reviewed">
                                                <button type="submit" class="btn btn-success btn-sm">Review</button>
                                            </form>
                                            <form method="POST" action="/bidboard/actions/update_report_status.php" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                                                    generate_csrf_token(),
                                                ) ?>">
                                                <input type="hidden" name="report_id" value="<?= $r[
                                                    "id"
                                                ] ?>">
                                                <input type="hidden" name="status" value="dismissed">
                                                <button type="submit" class="btn btn-ghost btn-sm">Dismiss</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-sm text-muted">Resolved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
