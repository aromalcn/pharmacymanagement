<?php
/**
 * admin/admin_login.php
 * Admin login page – secure authentication with bcrypt password verification
 */
// Don't show this header/footer, login has its own full-page layout
if (session_status() === PHP_SESSION_NONE)
    session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Use prepared statement to prevent SQL injection
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // password_verify checks the bcrypt hash
        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – PharmaCare</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-box">
        <div class="login-logo">💊</div>
        <h1>PharmaCare</h1>
        <p>Admin Portal – Sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter admin username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password"
                    required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%">
                Sign In →
            </button>
        </form>
        <p style="text-align:center; margin-top:1.25rem; font-size:.8rem; color:var(--gray-400)">
            Default: admin / admin123
        </p>
    </div>
</body>

</html>