<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'dashboard';

$counts = [];
$counts['patients']     = $pdo->query("SELECT COUNT(*) c FROM patients")->fetch()['c'];
$counts['doctors']      = $pdo->query("SELECT COUNT(*) c FROM doctors")->fetch()['c'];
$counts['staff']        = $pdo->query("SELECT COUNT(*) c FROM staff")->fetch()['c'];
$counts['appointments'] = $pdo->query("SELECT COUNT(*) c FROM appointments WHERE status IN ('Pending','Confirmed')")->fetch()['c'];
$counts['beds_free']    = $pdo->query("SELECT COUNT(*) c FROM beds WHERE status = 'Available'")->fetch()['c'];
$counts['beds_total']   = $pdo->query("SELECT COUNT(*) c FROM beds")->fetch()['c'];
$counts['unpaid_bills'] = $pdo->query("SELECT COUNT(*) c FROM billing WHERE payment_status != 'Paid'")->fetch()['c'];

$recentAppointments = $pdo->query("
    SELECT a.appointment_id, p.name AS patient_name, d.name AS doctor_name, a.appointment_date, a.appointment_time, a.status
    FROM appointments a
    JOIN patients p ON p.patient_id = a.patient_id
    JOIN doctors d ON d.doctor_id = a.doctor_id
    ORDER BY a.created_at DESC
    LIMIT 6
")->fetchAll();

function status_badge($status) {
    $map = [
        'Pending'   => 'badge-amber',
        'Confirmed' => 'badge-green',
        'Completed' => 'badge-gray',
        'Cancelled' => 'badge-coral',
        'Paid'      => 'badge-green',
        'Unpaid'    => 'badge-coral',
        'Partial'   => 'badge-amber',
        'Available' => 'badge-green',
        'Occupied'  => 'badge-coral',
    ];
    $cls = $map[$status] ?? 'badge-gray';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Dashboard</h1>
                <p class="welcome">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>. Here's today's overview.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Patients</div>
                <div class="stat-value"><?= $counts['patients'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Doctors on Roll</div>
                <div class="stat-value cyan"><?= $counts['doctors'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Staff Members</div>
                <div class="stat-value"><?= $counts['staff'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Appointments</div>
                <div class="stat-value cyan"><?= $counts['appointments'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Beds Available</div>
                <div class="stat-value"><?= $counts['beds_free'] ?> / <?= $counts['beds_total'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Unpaid Bills</div>
                <div class="stat-value coral"><?= $counts['unpaid_bills'] ?></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Recent Appointments</h2>
                <a href="appointments.php" class="btn btn-outline btn-sm">View all</a>
            </div>

            <?php if (empty($recentAppointments)): ?>
                <div class="empty-state">No appointments booked yet.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentAppointments as $a): ?>
                    <tr>
                        <td class="id-tag">#<?= $a['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($a['patient_name']) ?></td>
                        <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                        <td><?= htmlspecialchars(substr($a['appointment_time'],0,5)) ?></td>
                        <td><?= status_badge($a['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../js/script.js"></script>
</body>
</html>
