<?php
// Client registration page

session_name('bidboard_client');
session_start();

if (isset($_SESSION['client_id'])) {
    header('Location: /bidboard/client/dashboard.php');
    exit();
}

require_once '../includes/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Basic validation
    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email is already taken
        $stmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with that email already exists.';
            $stmt->close();
        } else {
            $stmt->close();

            // Hash password and insert new client
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins    = $conn->prepare("INSERT INTO clients (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $name, $email, $hashed);

            if ($ins->execute()) {
                // Auto-login after registration
                $_SESSION['client_id']   = $conn->insert_id;
                $_SESSION['client_name'] = $name;
                $ins->close();
                header('Location: /bidboard/client/dashboard.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
                $ins->close();
            }
        }
    }
}

$page_title  = 'Create Account';
$nav_context = 'public';
require_once '../includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Create account</h2>
        <p class="auth-sub">Start posting tasks and finding talent</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="name">Full name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Jane Doe"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Min. 6 characters"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm">Confirm password</label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    class="form-control"
                    placeholder="Repeat password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">
                Create account
            </button>
        </form>

        <p class="text-sm text-muted" style="text-align:center; margin-top:1.25rem;">
            Already have an account?
            <a href="/bidboard/auth/client_login.php" style="color:var(--accent);">Sign in</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
