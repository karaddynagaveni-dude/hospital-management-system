<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'appointments';

$error = '';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: appointments.php?deleted=1');
    exit;
}

// Update status quickly
if (isset($_GET['status']) && isset($_GET['id'])) {
    $allowed = ['Pending','Confirmed','Completed','Cancelled'];
    if (in_array($_GET['status'], $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE appointment_id=?");
        $stmt->execute([$_GET['status'], (int)$_GET['id']]);
        header('Location: appointments.php?updated=1');
        exit;
    }
}

// Book a new appointment (admin, on behalf of a patient)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? '';
    $doctor_id  = $_POST['doctor_id'] ?? '';
    $date       = $_POST['appointment_date'] ?? '';
    $time       = $_POST['appointment_time'] ?? '';

    if (!$patient_id || !$doctor_id || !$date || !$time) {
        $error = 'All fields are required to book an appointment.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?,?,?,?, 'Confirmed')");
        $stmt->execute([$patient_id, $doctor_id, $date, $time]);
        header('Location: appointments.php?added=1');
        exit;
    }
}

$appointments = $pdo->query("
    SELECT a.*, p.name AS patient_name, d.name AS doctor_name
    FROM appointments a
    JOIN patients p ON p.patient_id = a.patient_id
    JOIN doctors d ON d.doctor_id = a.doctor_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll();

$patients = $pdo->query("SELECT patient_id, name FROM patients ORDER BY name")->fetchAll();
$doctors  = $pdo->query("SELECT doctor_id, name, specialization FROM doctors ORDER BY name")->fetchAll();

function appt_badge($status) {
    $map = ['Pending' => 'badge-amber', 'Confirmed' => 'badge-green', 'Completed' => 'badge-gray', 'Cancelled' => 'badge-coral'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Appointment Scheduling</h1>
                <p class="welcome">Review, confirm and manage all patient appointments.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Appointment booked.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Appointment status updated.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Appointment deleted.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="panel">
            <div class="panel-head"><h2>Book Appointment (on behalf of a patient)</h2></div>

            <?php if (empty($patients) || empty($doctors)): ?>
                <div class="empty-state">Add at least one patient and one doctor before booking appointments.</div>
            <?php else: ?>
            <form method="POST" data-validate novalidate>
                <div class="form-inline-grid">
                    <div class="form-group">
                        <label>Patient</label>
                        <select name="patient_id" required>
                            <option value="">-- Select patient --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['patient_id'] ?>">#<?= $p['patient_id'] ?> — <?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Doctor</label>
                        <select name="doctor_id" required>
                            <option value="">-- Select doctor --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['doctor_id'] ?>">Dr. <?= htmlspecialchars($d['name']) ?> <?= $d['specialization'] ? '(' . htmlspecialchars($d['specialization']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="appointment_date" class="no-past" required>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="appointment_time" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Book Appointment</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>All Appointments (<?= count($appointments) ?>)</h2></div>
            <?php if (empty($appointments)): ?>
                <div class="empty-state">No appointments have been booked yet.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td class="id-tag">#<?= $a['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($a['patient_name']) ?></td>
                        <td>Dr. <?= htmlspecialchars($a['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                        <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                        <td><?= appt_badge($a['status']) ?></td>
                        <td class="action-icons">
                            <?php if ($a['status'] !== 'Confirmed'): ?>
                                <a href="appointments.php?status=Confirmed&id=<?= $a['appointment_id'] ?>" title="Confirm">✓</a>
                            <?php endif; ?>
                            <?php if ($a['status'] !== 'Completed'): ?>
                                <a href="appointments.php?status=Completed&id=<?= $a['appointment_id'] ?>" title="Mark completed">●</a>
                            <?php endif; ?>
                            <?php if ($a['status'] !== 'Cancelled'): ?>
                                <a href="appointments.php?status=Cancelled&id=<?= $a['appointment_id'] ?>" class="danger" title="Cancel">✕</a>
                            <?php endif; ?>
                            <a href="appointments.php?delete=<?= $a['appointment_id'] ?>" class="danger" title="Delete" data-confirm="Delete this appointment record?">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../js/script.js"></script>
</body>
</html>
