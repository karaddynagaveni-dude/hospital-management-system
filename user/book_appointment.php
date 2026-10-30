<?php
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'book';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $date      = $_POST['appointment_date'] ?? '';
    $time      = $_POST['appointment_time'] ?? '';

    if (!$doctor_id || !$date || !$time) {
        $error = 'Please select a doctor, date and time.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?,?,?,?, 'Pending')");
        $stmt->execute([$_SESSION['patient_id'], $doctor_id, $date, $time]);
        header('Location: dashboard.php?booked=1');
        exit;
    }
}

$doctors = $pdo->query("SELECT * FROM doctors WHERE availability != 'On Leave' ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/user_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Book an Appointment</h1>
                <p class="welcome">Choose a doctor and a convenient time slot.</p>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="panel">
            <?php if (empty($doctors)): ?>
                <div class="empty-state">No doctors are available for booking right now. Please check back later.</div>
            <?php else: ?>
            <form method="POST" data-validate novalidate>
                <div class="form-group">
                    <label>Doctor</label>
                    <select name="doctor_id" required>
                        <option value="">-- Select doctor --</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= $d['doctor_id'] ?>">
                                Dr. <?= htmlspecialchars($d['name']) ?>
                                <?= $d['specialization'] ? ' — ' . htmlspecialchars($d['specialization']) : '' ?>
                                <?= $d['department'] ? ' (' . htmlspecialchars($d['department']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Date</label>
                        <input type="date" name="appointment_date" class="no-past" required>
                    </div>
                    <div class="form-group">
                        <label>Preferred Time</label>
                        <input type="time" name="appointment_time" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Request Appointment</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../js/script.js"></script>
</body>
</html>
