<?php
// =============================================
// index.php — READ (All Snippets)
// =============================================

require 'config/db.php';
$pageTitle = 'All Snippets';

// ---- FETCH ALL SNIPPETS ----
// Also count how many files each snippet has using a subquery
// This lets us show "3 files" on the index card
$stmt = $pdo->query("
    SELECT
        s.id,
        s.title,
        s.language,
        s.description,
        s.created_at,
        GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ',') AS tags,
        COUNT(DISTINCT sf.id) AS file_count
    FROM snippets s
    LEFT JOIN snippet_tags st ON s.id = st.snippet_id
    LEFT JOIN tags t          ON st.tag_id = t.id
    LEFT JOIN snippet_files sf ON s.id = sf.snippet_id
    GROUP BY s.id
    ORDER BY s.created_at DESC
");
$snippets = $stmt->fetchAll();

// ---- FETCH LANGUAGES for dropdown ----
$langStmt  = $pdo->query("SELECT DISTINCT language FROM snippets ORDER BY language ASC");
$languages = $langStmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== SEARCH AND FILTER ===== -->
<div class="search-bar">
    <input type="text" id="search" placeholder="Search snippets...">
    <select id="lang-filter">
        <option value="">All Languages</option>
        <?php foreach ($languages as $lang): ?>
            <option value="<?= htmlspecialchars($lang['language']) ?>">
                <?= htmlspecialchars($lang['language']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- ===== SNIPPET LIST ===== -->
<?php if (empty($snippets)): ?>
    <p class="empty">No snippets yet. <a href="create.php">Add your first one.</a></p>
<?php else: ?>
    <?php foreach ($snippets as $snippet): ?>
        <div class="snippet-row" data-language="<?= htmlspecialchars($snippet['language']) ?>">

            <div class="snippet-header">
                <span class="snippet-title"><?= htmlspecialchars($snippet['title']) ?></span>
                <span class="badge"><?= htmlspecialchars(strtoupper($snippet['language'])) ?></span>
            </div>

            <?php if (!empty($snippet['description'])): ?>
                <div class="snippet-desc"><?= htmlspecialchars($snippet['description']) ?></div>
            <?php endif; ?>

            <!-- Show how many files this snippet has -->
            <div class="file-count">
                <?= $snippet['file_count'] ?> file<?= $snippet['file_count'] != 1 ? 's' : '' ?>
            </div>

            <div class="snippet-footer">
                <div class="tags">
                    <?php if (!empty($snippet['tags'])): ?>
                        <?php foreach (explode(',', $snippet['tags']) as $tag): ?>
                            <span class="tag">#<?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <a href="view.php?id=<?= $snippet['id'] ?>" class="btn">View</a>
                    <a href="edit.php?id=<?= $snippet['id'] ?>" class="btn">Edit</a>
                    <form method="POST" action="delete.php"
                          onsubmit="return confirm('Delete this snippet?')">
                        <input type="hidden" name="id" value="<?= $snippet['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
