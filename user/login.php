<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['patient_id'])) {
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
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE username = ?");
        $stmt->execute([$username]);
        $patient = $stmt->fetch();

        if ($patient && password_verify($password, $patient['password'])) {
            $_SESSION['patient_id'] = $patient['patient_id'];
            $_SESSION['patient_name'] = $patient['name'];
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
<title>Patient Login - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a href="../index.html" class="back-link">&larr; Back to home</a>
        <div class="auth-header">
            <span class="tag">Patient Portal</span>
            <h2>Welcome back</h2>
            <p>Sign in to manage your appointments &amp; records</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" data-validate novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="auth-footer">New patient? <a href="register.php">Create an account</a></div>
        <div class="auth-footer">Are you hospital staff? <a href="../admin/login.php">Go to admin login</a></div>
    </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
