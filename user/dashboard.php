<?php
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'dashboard';

$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$_SESSION['patient_id']]);
$patient = $stmt->fetch();

$appointments = $pdo->prepare("
    SELECT a.*, d.name AS doctor_name, d.specialization
    FROM appointments a
    JOIN doctors d ON d.doctor_id = a.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments->execute([$_SESSION['patient_id']]);
$appointments = $appointments->fetchAll();

$bills = $pdo->prepare("SELECT * FROM billing WHERE patient_id = ? ORDER BY bill_date DESC");
$bills->execute([$_SESSION['patient_id']]);
$bills = $bills->fetchAll();

$bedInfo = $pdo->prepare("SELECT * FROM beds WHERE patient_id = ?");
$bedInfo->execute([$_SESSION['patient_id']]);
$bedInfo = $bedInfo->fetch();

function u_appt_badge($status) {
    $map = ['Pending' => 'badge-amber', 'Confirmed' => 'badge-green', 'Completed' => 'badge-gray', 'Cancelled' => 'badge-coral'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
function u_pay_badge($status) {
    $map = ['Paid' => 'badge-green', 'Unpaid' => 'badge-coral', 'Partial' => 'badge-amber'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/user_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>My Overview</h1>
                <p class="welcome">Welcome, <?= htmlspecialchars($patient['name']) ?>.</p>
            </div>
        </div>

        <?php if (isset($_GET['welcome'])): ?>
            <div class="alert alert-success">Account created successfully. Welcome to Hospital Management System!</div>
        <?php endif; ?>
        <?php if (isset($_GET['booked'])): ?>
            <div class="alert alert-success">Appointment request sent. The hospital will confirm it shortly.</div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Patient ID</div>
                <div class="stat-value cyan">#<?= $patient['patient_id'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Appointments</div>
                <div class="stat-value"><?= count($appointments) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Bed / Ward</div>
                <div class="stat-value" style="font-size:1.2rem;"><?= $bedInfo ? htmlspecialchars($bedInfo['ward_type']) . ' (#' . $bedInfo['bed_id'] . ')' : 'Not admitted' ?></div>
            </div>
        </div>

        <div class="two-col">
            <div class="panel">
                <div class="panel-head">
                    <h2>My Appointments</h2>
                    <a href="book_appointment.php" class="btn btn-primary btn-sm">Book New</a>
                </div>
                <?php if (empty($appointments)): ?>
                    <div class="empty-state">You have no appointments yet. Book one to get started.</div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td>Dr. <?= htmlspecialchars($a['doctor_name']) ?><br><small style="color:var(--ink-soft)"><?= htmlspecialchars($a['specialization'] ?: '') ?></small></td>
                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                            <td><?= u_appt_badge($a['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>My Bills</h2></div>
                <?php if (empty($bills)): ?>
                    <div class="empty-state">No bills yet.</div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($bills as $b): ?>
                        <tr>
                            <td>₹<?= number_format($b['total_amount'], 2) ?></td>
                            <td><?= u_pay_badge($b['payment_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>My Profile</h2></div>
            <div class="form-inline-grid">
                <div><div class="stat-label">Age</div><div><?= htmlspecialchars($patient['age'] ?? '—') ?></div></div>
                <div><div class="stat-label">Gender</div><div><?= htmlspecialchars($patient['gender'] ?? '—') ?></div></div>
                <div><div class="stat-label">Contact</div><div><?= htmlspecialchars($patient['contact_no'] ?? '—') ?></div></div>
                <div><div class="stat-label">Address</div><div><?= htmlspecialchars($patient['address'] ?? '—') ?></div></div>
                <div><div class="stat-label">Diagnosis</div><div><?= htmlspecialchars($patient['disease'] ?: '—') ?></div></div>
            </div>
        </div>
    </main>
</div>
<script src="../js/script.js"></script>
</body>
</html>
