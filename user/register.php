<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['patient_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $age        = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
    $gender     = $_POST['gender'] ?? null;
    $address    = trim($_POST['address'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if ($name === '' || $username === '' || $password === '') {
        $error = 'Name, username and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $pdo->prepare("SELECT patient_id FROM patients WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'That username is already taken. Please choose another.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO patients (username, password, name, age, gender, address, contact_no) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$username, $hashed, $name, $age, $gender, $address, $contact_no]);

            $_SESSION['patient_id'] = $pdo->lastInsertId();
            $_SESSION['patient_name'] = $name;
            header('Location: dashboard.php?welcome=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Registration - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="max-width:520px;">
        <a href="../index.html" class="back-link">&larr; Back to home</a>
        <div class="auth-header">
            <span class="tag">Patient Portal</span>
            <h2>Create your account</h2>
            <p>Register once to book appointments and track your records</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" data-validate novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required autofocus value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" min="0" max="130" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">-- Select --</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= (($_POST['gender'] ?? '') === $g) ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact No.</label>
                    <input type="text" name="contact_no" value="<?= htmlspecialchars($_POST['contact_no'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Choose Username</label>
                <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-footer">Already registered? <a href="login.php">Login here</a></div>
    </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
