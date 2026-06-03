<?php
// =============================================
// edit.php — UPDATE (Existing Snippet)
// Loads all files into editable blocks
// =============================================

require 'config/db.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = (int) $_POST['id'];
    $title       = trim($_POST['title']);
    $language    = trim($_POST['language']);
    $description = trim($_POST['description']);
    $tagsRaw     = trim($_POST['tags']);
    $filenames   = $_POST['filename'] ?? [];
    $codes       = $_POST['code']     ?? [];

    $hasCode = false;
    foreach ($codes as $c) {
        if (trim($c) !== '') {
            $hasCode = true;
            break;
        }
    }

    if (empty($title) || empty($language) || !$hasCode) {
        $error = "Title, language, and at least one code file are required.";
    } else {

        // ---- UPDATE SNIPPET ----
        $pdo->prepare("
            UPDATE snippets SET title=?, language=?, description=? WHERE id=?
        ")->execute([$title, $language, $description, $id]);

        // ---- RE-SYNC FILES ----
        // Delete all old files for this snippet and re-insert
        // Simpler than tracking which files were added/removed/edited
        $pdo->prepare("DELETE FROM snippet_files WHERE snippet_id = ?")->execute([$id]);

        foreach ($filenames as $i => $filename) {
            $filename = trim($filename);
            $code     = $codes[$i] ?? '';
            if ($filename === '' && trim($code) === '') continue;
            if ($filename === '') $filename = 'file-' . ($i + 1);

            $pdo->prepare("
                INSERT INTO snippet_files (snippet_id, filename, code, sort_order)
                VALUES (?, ?, ?, ?)
            ")->execute([$id, $filename, $code, $i]);
        }

        // ---- RE-SYNC TAGS ----
        $pdo->prepare("DELETE FROM snippet_tags WHERE snippet_id = ?")->execute([$id]);

        if (!empty($tagsRaw)) {
            foreach (explode(',', $tagsRaw) as $tag) {
                $tag = strtolower(trim($tag));
                if ($tag === '') continue;

                $check = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                $check->execute([$tag]);
                $existing = $check->fetch();

                if ($existing) {
                    $tagId = $existing['id'];
                } else {
                    $pdo->prepare("INSERT INTO tags (name) VALUES (?)")->execute([$tag]);
                    $tagId = $pdo->lastInsertId();
                }

                $pdo->prepare("INSERT INTO snippet_tags (snippet_id, tag_id) VALUES (?, ?)")
                    ->execute([$id, $tagId]);
            }
        }

        $_SESSION['msg'] = "Snippet updated!";
        header('Location: view.php?id=' . $id);
        exit;
    }
}

// ---- LOAD EXISTING DATA ----
$stmt = $pdo->prepare("SELECT * FROM snippets WHERE id = ?");
$stmt->execute([$id]);
$snippet = $stmt->fetch();
if (!$snippet) die("Snippet not found.");

// Load existing files ordered by sort_order
$fileStmt = $pdo->prepare("
    SELECT * FROM snippet_files WHERE snippet_id = ? ORDER BY sort_order ASC, id ASC
");
$fileStmt->execute([$id]);
$existingFiles = $fileStmt->fetchAll();

// Load existing tags as comma-separated string
$tagStmt = $pdo->prepare("
    SELECT t.name FROM tags t
    JOIN snippet_tags st ON t.id = st.tag_id
    WHERE st.snippet_id = ? ORDER BY t.name ASC
");
$tagStmt->execute([$id]);
$existingTags = implode(', ', array_column($tagStmt->fetchAll(), 'name'));

// If POST failed validation, use POST data to repopulate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $snippet['title']       = $_POST['title'];
    $snippet['language']    = $_POST['language'];
    $snippet['description'] = $_POST['description'];
    $existingTags           = $_POST['tags'];
    // Rebuild files from POST data for repopulation
    $existingFiles = [];
    foreach (($_POST['filename'] ?? []) as $i => $fn) {
        $existingFiles[] = ['filename' => $fn, 'code' => $_POST['code'][$i] ?? ''];
    }
}

$pageTitle = 'Edit: ' . $snippet['title'];
include 'includes/header.php';
?>

<p style="margin-bottom:1.5rem;">
    <a href="view.php?id=<?= $id ?>" style="color:var(--muted);font-size:13px;">← Back to snippet</a>
</p>

<div style="font-weight:bold;margin-bottom:1.2rem;">Edit Snippet</div>

<?php if (isset($error)): ?>
    <p style="color:#cc0000;font-size:13px;margin-bottom:1rem;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="POST" action="edit.php">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($snippet['title']) ?>">
    </div>

    <div class="form-group">
        <label>Primary Language *</label>
        <select name="language">
            <option value="">-- Select Language --</option>
            <?php
            $langs = ['bash', 'c', 'cpp', 'css', 'html', 'java', 'javascript', 'json', 'php', 'python', 'sql', 'typescript', 'git', 'kotlin'];
            foreach ($langs as $l):
                $selected = ($snippet['language'] === $l) ? 'selected' : '';
            ?>
                <option value="<?= $l ?>" <?= $selected ?>><?= strtoupper($l) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Description (optional)</label>
        <input type="text" name="description"
            value="<?= htmlspecialchars($snippet['description']) ?>">
    </div>

    <div class="form-group">
        <label>Tags (comma separated)</label>
        <input type="text" name="tags" value="<?= htmlspecialchars($existingTags) ?>">
    </div>

    <!-- ===== FILES SECTION ===== -->
    <div style="font-size:13px;color:var(--muted);margin-bottom:0.5rem;">
        Files * — edit existing files or add new ones
    </div>

    <div id="files-container">
        <!-- Pre-fill file blocks from DB data -->
        <?php foreach ($existingFiles as $file): ?>
            <div class="file-block">
                <div class="file-block-header">
                    <input type="text" name="filename[]"
                        value="<?= htmlspecialchars($file['filename']) ?>"
                        placeholder="e.g. style.css">
                    <button type="button" class="btn btn-danger remove-file">Remove</button>
                </div>
                <textarea name="code[]"><?= htmlspecialchars($file['code']) ?></textarea>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="add-file" class="btn btn-muted" style="margin-bottom:1.2rem;">
        + Add File
    </button>

    <br>
    <button type="submit" class="btn">Update Snippet</button>

</form>

<?php include 'includes/footer.php'; ?>