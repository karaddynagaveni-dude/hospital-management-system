<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
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
<title>Admin Login - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a href="../index.html" class="back-link">&larr; Back to home</a>
        <div class="auth-header">
            <span class="tag">Admin Portal</span>
            <h2>Sign in to manage the hospital</h2>
            <p>Doctors, staff, patients, beds &amp; billing</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-validate novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login as Admin</button>
        </form>

        <div class="demo-hint">Demo credentials — username: <strong>admin</strong> &nbsp;|&nbsp; password: <strong>admin123</strong></div>

        <div class="auth-footer">Not an admin? <a href="../user/login.php">Go to patient login</a></div>
    </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
