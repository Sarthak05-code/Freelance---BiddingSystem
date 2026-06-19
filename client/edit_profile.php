<?php
// Client: edit profile — update name, email, and optionally password

require_once '../includes/auth_client.php';
require_once '../includes/db.php';

$client_id = $_SESSION['client_id'];

// Fetch current client data
$stmt = $conn->prepare("SELECT id, name, email FROM clients WHERE id = ?");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Please go back and try again.');
    }
    $name        = trim($_POST['name']         ?? '');
    $email       = trim($_POST['email']        ?? '');
    $new_pass    = trim($_POST['new_password'] ?? '');    // optional — blank means don't change
    $confirm     = trim($_POST['confirm']      ?? '');
    $current_raw = trim($_POST['current_password'] ?? ''); // required to confirm identity

    // Basic field validation
    if ($name === '' || $email === '') {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($current_raw === '') {
        $error = 'Enter your current password to save changes.';
    } else {
        // Verify the current password before allowing any change
        $pass_stmt = $conn->prepare("SELECT password FROM clients WHERE id = ?");
        $pass_stmt->bind_param('i', $client_id);
        $pass_stmt->execute();
        $row = $pass_stmt->get_result()->fetch_assoc();
        $pass_stmt->close();

        if (!password_verify($current_raw, $row['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Check if new email is taken by another client
            $email_check = $conn->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
            $email_check->bind_param('si', $email, $client_id);
            $email_check->execute();
            $email_check->store_result();
            $email_taken = $email_check->num_rows > 0;
            $email_check->close();

            if ($email_taken) {
                $error = 'That email is already used by another account.';
            } elseif ($new_pass !== '' && strlen($new_pass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($new_pass !== '' && $new_pass !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                // All good — build the update query
                if ($new_pass !== '') {
                    // Update name, email, and password
                    $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                    $upd    = $conn->prepare("UPDATE clients SET name = ?, email = ?, password = ? WHERE id = ?");
                    $upd->bind_param('sssi', $name, $email, $hashed, $client_id);
                } else {
                    // Update only name and email, leave password unchanged
                    $upd = $conn->prepare("UPDATE clients SET name = ?, email = ? WHERE id = ?");
                    $upd->bind_param('ssi', $name, $email, $client_id);
                }

                if ($upd->execute()) {
                    // Update the session name so navbar reflects change
                    $_SESSION['client_name'] = $name;
                    $success = 'Profile updated successfully.';

                    // Refresh local $client var so the form shows updated values
                    $client['name']  = $name;
                    $client['email'] = $email;
                } else {
                    $error = 'Update failed. Please try again.';
                }
                $upd->close();
            }
        }
    }
}

$page_title  = 'Edit Profile';
$nav_context = 'client';
require_once '../includes/header.php';
?>

<div class="page-wrap">
    <div class="container" style="max-width:600px;">

        <div class="page-header">
            <h1>Edit profile</h1>
            <p>Update your name, email, or password.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

                    <!-- Name -->
                    <div class="form-group">
                        <label class="form-label" for="name">Full name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            value="<?= htmlspecialchars($client['name']) ?>"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($client['email']) ?>"
                            required>
                    </div>

                    <!-- Divider for password section -->
                    <div style="border-top:1px solid var(--border); margin:1.5rem 0 1.25rem;">
                    </div>
                    <p class="text-sm text-muted" style="margin-bottom:1rem;">
                        Leave the new password fields blank if you don't want to change it.
                    </p>

                    <!-- New password (optional) -->
                    <div class="form-group">
                        <label class="form-label" for="new_password">New password</label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-control"
                            placeholder="Min. 6 characters"
                            autocomplete="new-password">
                    </div>

                    <!-- Confirm new password -->
                    <div class="form-group">
                        <label class="form-label" for="confirm">Confirm new password</label>
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            class="form-control"
                            placeholder="Repeat new password"
                            autocomplete="new-password">
                    </div>

                    <!-- Divider -->
                    <div style="border-top:1px solid var(--border); margin:1.5rem 0 1.25rem;"></div>

                    <!-- Current password — always required to save -->
                    <div class="form-group">
                        <label class="form-label" for="current_password">Current password</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            class="form-control"
                            placeholder="Required to save any changes"
                            autocomplete="current-password"
                            required>
                        <p class="form-hint">We verify your identity before saving changes.</p>
                    </div>

                    <!-- Actions -->
                    <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="/bidboard/client/dashboard.php" class="btn btn-ghost">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>