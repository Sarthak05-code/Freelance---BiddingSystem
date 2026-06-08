<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- Makes the page responsive on mobile screens -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- $pageTitle is set in each page before including this header -->
    <title><?= isset($pageTitle) ? $pageTitle . ' — Snippet Vault' : 'Snippet Vault' ?></title>

    <!-- Prism.js CSS — handles the colors for syntax highlighting -->
    <link rel="stylesheet" href="/snipvault/lib/prism.css">

    <!-- Our own minimal stylesheet -->
    <link rel="stylesheet" href="/snipvault/css/style.css">
</head>

<body>

    <!-- ===== TOP NAVIGATION BAR ===== -->
    <nav class="navbar">
        <a href="/snipvault/index.php" class="nav-brand">Snippet Vault</a>
        <div style="display:flex; gap:0.5rem; align-items:center;">
            <!-- Dark mode toggle — JS in main.js handles the click -->
            <button class="theme-toggle" id="theme-toggle">◑ Dark</button>
            <a href="/snipvault/create.php" class="btn">+ New Snippet</a>
        </div>
    </nav>

    <!-- ===== FLASH MESSAGE ===== -->
    <?php
    // Flash messages are stored in the session to survive a redirect
    // e.g. after saving, we redirect to index and show "Snippet saved!"
    session_start();
    if (isset($_SESSION['msg'])) {
        echo '<div class="flash">' . htmlspecialchars($_SESSION['msg']) . '</div>';
        // Unset after displaying so it doesn't show again on next page load
        unset($_SESSION['msg']);
    }
    ?>

    <!-- ===== MAIN CONTENT WRAPPER ===== -->
    <!-- Each page's content will be inserted below this line -->
    <main class="container">