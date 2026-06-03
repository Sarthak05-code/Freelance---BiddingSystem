<?php
// =============================================
// view.php — READ (Single Snippet)
// Shows all files as tabs with syntax highlighting
// =============================================

require 'config/db.php';

$id = (int) $_GET['id'];

// ---- FETCH SNIPPET ----
$stmt = $pdo->prepare("SELECT * FROM snippets WHERE id = ?");
$stmt->execute([$id]);
$snippet = $stmt->fetch();

if (!$snippet) die("Snippet not found.");

// ---- FETCH FILES for this snippet (ordered by sort_order) ----
$fileStmt = $pdo->prepare("
    SELECT * FROM snippet_files
    WHERE snippet_id = ?
    ORDER BY sort_order ASC, id ASC
");
$fileStmt->execute([$id]);
$files = $fileStmt->fetchAll();

// ---- FETCH TAGS ----
$tagStmt = $pdo->prepare("
    SELECT t.name FROM tags t
    JOIN snippet_tags st ON t.id = st.tag_id
    WHERE st.snippet_id = ?
    ORDER BY t.name ASC
");
$tagStmt->execute([$id]);
$tags = $tagStmt->fetchAll();

$pageTitle = $snippet['title'];
include 'includes/header.php';
?>

<p style="margin-bottom:1rem;">
    <a href="index.php" style="color:var(--muted);font-size:13px;">← Back to all snippets</a>
</p>

<div class="page-title"><?= htmlspecialchars($snippet['title']) ?></div>

<div class="meta">
    <?= htmlspecialchars(strtoupper($snippet['language'])) ?>
    &nbsp;·&nbsp;
    <?= count($files) ?> file<?= count($files) != 1 ? 's' : '' ?>
    &nbsp;·&nbsp;
    Added <?= date('M d, Y', strtotime($snippet['created_at'])) ?>
</div>

<?php if (!empty($snippet['description'])): ?>
    <p style="font-size:13px;margin-bottom:0.8rem;color:var(--muted);">
        <?= htmlspecialchars($snippet['description']) ?>
    </p>
<?php endif; ?>

<?php if (!empty($tags)): ?>
    <div class="tags" style="margin-bottom:1rem;">
        <?php foreach ($tags as $tag): ?>
            <span class="tag">#<?= htmlspecialchars($tag['name']) ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===== FILE TABS ===== -->
<?php if (!empty($files)): ?>

    <!-- TAB BUTTONS — one per file -->
    <div class="tabs">
        <?php foreach ($files as $i => $file): ?>
            <!--
                data-tab matches the panel id below (panel-0, panel-1 etc.)
                First tab gets 'active' class by default
            -->
            <button class="tab-btn <?= $i === 0 ? 'active' : '' ?>"
                    data-tab="<?= $i ?>">
                <?= htmlspecialchars($file['filename']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- TAB PANELS — one per file, only active one is visible -->
    <?php foreach ($files as $i => $file): ?>

        <!--
            id="panel-X" — JS uses this to show/hide the correct panel
            First panel gets 'active' class by default
        -->
        <div id="panel-<?= $i ?>"
             class="tab-panel <?= $i === 0 ? 'active' : '' ?>">

            <div class="code-block">
                <!-- Copy button — JS handles click via event delegation -->
                <button class="btn copy-btn">Copy</button>

                <!--
                    Prism needs class="language-X" to know how to highlight
                    We detect the language from the filename extension
                    e.g. "style.css" → language-css, "app.js" → language-javascript
                -->
                <?php
                // ---- DETECT LANGUAGE FROM FILENAME EXTENSION ----
                $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                $langMap = [
                    'html' => 'html',   'htm'  => 'html',
                    'css'  => 'css',    'js'   => 'javascript',
                    'php'  => 'php',    'sql'  => 'sql',
                    'ts'   => 'typescript', 'json' => 'json',
                    'py'   => 'python', 'java' => 'java',
                    'c'    => 'c',      'cpp'  => 'cpp',
                    'sh'   => 'bash',   'bash' => 'bash',
                ];
                // Default to the snippet's primary language if extension not recognised
                $fileLang = $langMap[$ext] ?? $snippet['language'];
                ?>
                <pre><code class="language-<?= htmlspecialchars($fileLang) ?>"><?= htmlspecialchars($file['code']) ?></code></pre>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>
    <p class="empty">No files found for this snippet.</p>
<?php endif; ?>

<!-- ACTION BUTTONS -->
<div class="actions" style="margin-top:1.2rem;">
    <a href="edit.php?id=<?= $snippet['id'] ?>" class="btn">Edit</a>
    <form method="POST" action="delete.php"
          onsubmit="return confirm('Delete this snippet?')"
          style="display:inline;">
        <input type="hidden" name="id" value="<?= $snippet['id'] ?>">
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
