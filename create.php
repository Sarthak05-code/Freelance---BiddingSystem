<?php
// =============================================
// create.php — CREATE (New Snippet)
// Handles multiple files per snippet
// =============================================

require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize main snippet fields
    $title       = trim($_POST['title']);
    $language    = trim($_POST['language']);
    $description = trim($_POST['description']);
    $tagsRaw     = trim($_POST['tags']);

    // $_POST['filename'] and $_POST['code'] are arrays
    // e.g. filename[] = ['index.html', 'style.css', 'app.js']
    //      code[]     = ['<html>...', 'body {...}', 'const x...']
    $filenames = $_POST['filename'] ?? [];
    $codes     = $_POST['code']     ?? [];

    // Validation — must have title, language, and at least one non-empty file
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

        // ---- INSERT SNIPPET ----
        $stmt = $pdo->prepare("
            INSERT INTO snippets (title, language, description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$title, $language, $description]);
        $snippetId = $pdo->lastInsertId();

        // ---- INSERT FILES ----
        // Loop through each file the user added
        // $filenames and $codes are parallel arrays — same index = same file
        foreach ($filenames as $i => $filename) {
            $filename = trim($filename);
            $code     = $codes[$i] ?? '';

            // Skip empty file blocks
            if ($filename === '' && trim($code) === '') continue;

            // Use a default filename if the user left it blank
            if ($filename === '') $filename = 'file-' . ($i + 1);

            $fileStmt = $pdo->prepare("
                INSERT INTO snippet_files (snippet_id, filename, code, sort_order)
                VALUES (?, ?, ?, ?)
            ");
            // sort_order = $i so files display in the order they were added
            $fileStmt->execute([$snippetId, $filename, $code, $i]);
        }

        // ---- INSERT TAGS ----
        if (!empty($tagsRaw)) {
            $tagList = explode(',', $tagsRaw);
            foreach ($tagList as $tag) {
                $tag = strtolower(trim($tag));
                if ($tag === '') continue;

                $check = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                $check->execute([$tag]);
                $existing = $check->fetch();

                if ($existing) {
                    $tagId = $existing['id'];
                } else {
                    $insert = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                    $insert->execute([$tag]);
                    $tagId = $pdo->lastInsertId();
                }

                $pdo->prepare("INSERT INTO snippet_tags (snippet_id, tag_id) VALUES (?, ?)")
                    ->execute([$snippetId, $tagId]);
            }
        }

        $_SESSION['msg'] = "Snippet saved!";
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'New Snippet';
include 'includes/header.php';
?>

<p style="margin-bottom:1.5rem;">
    <a href="index.php" style="color:var(--muted);font-size:13px;">← Back</a>
</p>

<div style="font-weight:bold;margin-bottom:1.2rem;">New Snippet</div>

<?php if (isset($error)): ?>
    <p style="color:#cc0000;font-size:13px;margin-bottom:1rem;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="POST" action="create.php">

    <!-- TITLE -->
    <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" placeholder="e.g. Navbar Component"
            value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
    </div>

    <!-- LANGUAGE -->
    <div class="form-group">
        <label>Primary Language *</label>
        <select name="language">
            <option value="">-- Select Language --</option>
            <?php
            $langs = ['bash', 'c', 'cpp', 'css', 'git', 'html', 'java', 'javascript', 'json', 'kotlin', 'php', 'python', 'sql', 'typescript'];
            foreach ($langs as $l):
                $selected = (isset($_POST['language']) && $_POST['language'] === $l) ? 'selected' : '';
            ?>
                <option value="<?= $l ?>" <?= $selected ?>><?= strtoupper($l) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- DESCRIPTION -->
    <div class="form-group">
        <label>Description (optional)</label>
        <input type="text" name="description" placeholder="What does this snippet do?"
            value="<?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?>">
    </div>

    <!-- TAGS -->
    <div class="form-group">
        <label>Tags (comma separated)</label>
        <input type="text" name="tags" placeholder="e.g. html, css, navbar"
            value="<?= isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : '' ?>">
    </div>

    <!-- ===== FILES SECTION ===== -->
    <div style="font-size:13px;color:var(--muted);margin-bottom:0.5rem;">
        Files * — add one file per block, click "+ Add File" for more
    </div>

    <!-- File blocks are added/removed here by JS -->
    <div id="files-container">

        <!-- First file block — always present, remove button hidden by JS if only one -->
        <div class="file-block">
            <div class="file-block-header">
                <!-- name="filename[]" — [] tells PHP to treat this as an array -->
                <input type="text" name="filename[]" placeholder="e.g. index.html">
                <button type="button" class="btn btn-danger remove-file">Remove</button>
            </div>
            <!-- name="code[]" — parallel array to filename[] -->
            <textarea name="code[]" placeholder="Paste your code here..."></textarea>
        </div>

    </div>

    <!-- Button to add another file block — JS handles the click -->
    <button type="button" id="add-file" class="btn btn-muted" style="margin-bottom:1.2rem;">
        + Add File
    </button>

    <br>
    <button type="submit" class="btn">Save Snippet</button>

</form>

<?php include 'includes/footer.php'; ?>