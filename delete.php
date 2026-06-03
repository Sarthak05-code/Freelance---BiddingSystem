<?php
// =============================================
// delete.php — DELETE (Snippet)
// No HTML page — just a POST handler that
// deletes the snippet and redirects back
// =============================================

require 'config/db.php';

// Only process if this was a POST request
// This prevents someone from deleting by just visiting delete.php?id=3 in the browser
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the snippet ID from the hidden form field
    $id = (int) $_POST['id'];

    // ---- DELETE THE SNIPPET ----
    // Because we set ON DELETE CASCADE on snippet_tags in the DB schema,
    // deleting the snippet automatically deletes its rows in snippet_tags too
    // We don't need to manually delete from snippet_tags
    $stmt = $pdo->prepare("DELETE FROM snippets WHERE id = ?");
    $stmt->execute([$id]);

    // Store success message in session to display after redirect
    $_SESSION['msg'] = "Snippet deleted.";
}

// Redirect back to the snippet list
// Always redirect after a POST (Post/Redirect/Get pattern)
// This prevents the "resubmit form?" browser warning on refresh
header('Location: index.php');
exit;
