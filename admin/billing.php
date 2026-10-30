<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'billing';

$error = '';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM billing WHERE bill_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: billing.php?deleted=1');
    exit;
}

if (isset($_GET['mark_paid'])) {
    $stmt = $pdo->prepare("UPDATE billing SET payment_status='Paid' WHERE bill_id=?");
    $stmt->execute([(int)$_GET['mark_paid']]);
    header('Location: billing.php?updated=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id     = $_POST['patient_id'] ?? '';
    $treatment_cost = $_POST['treatment_cost'] !== '' ? (float)$_POST['treatment_cost'] : 0;
    $room_charges   = $_POST['room_charges'] !== '' ? (float)$_POST['room_charges'] : 0;
    $payment_status = $_POST['payment_status'] ?? 'Unpaid';

    if (!$patient_id) {
        $error = 'Please select a patient for this bill.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO billing (patient_id, treatment_cost, room_charges, payment_status) VALUES (?,?,?,?)");
        $stmt->execute([$patient_id, $treatment_cost, $room_charges, $payment_status]);
        header('Location: billing.php?added=1');
        exit;
    }
}

$bills = $pdo->query("
    SELECT b.*, p.name AS patient_name
    FROM billing b
    JOIN patients p ON p.patient_id = b.patient_id
    ORDER BY b.bill_date DESC
")->fetchAll();

$patients = $pdo->query("SELECT patient_id, name FROM patients ORDER BY name")->fetchAll();

$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) t FROM billing WHERE payment_status='Paid'")->fetch()['t'];
$pendingRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) t FROM billing WHERE payment_status != 'Paid'")->fetch()['t'];

function pay_badge($status) {
    $map = ['Paid' => 'badge-green', 'Unpaid' => 'badge-coral', 'Partial' => 'badge-amber'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billing - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Billing &amp; Invoices</h1>
                <p class="welcome">Generate and track patient bills.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Bill created.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Bill marked as paid.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Bill deleted.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Collected</div>
                <div class="stat-value">₹<?= number_format($totalRevenue, 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Collection</div>
                <div class="stat-value coral">₹<?= number_format($pendingRevenue, 2) ?></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Create New Bill</h2></div>
            <?php if (empty($patients)): ?>
                <div class="empty-state">Add a patient first before creating a bill.</div>
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
                        <label>Treatment Cost (₹)</label>
                        <input type="number" step="0.01" min="0" name="treatment_cost" value="0">
                    </div>
                    <div class="form-group">
                        <label>Room Charges (₹)</label>
                        <input type="number" step="0.01" min="0" name="room_charges" value="0">
                    </div>
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select name="payment_status">
                            <?php foreach (['Unpaid','Partial','Paid'] as $s): ?>
                                <option value="<?= $s ?>"><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Generate Bill</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>All Bills (<?= count($bills) ?>)</h2></div>
            <?php if (empty($bills)): ?>
                <div class="empty-state">No bills generated yet.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>ID</th><th>Patient</th><th>Treatment</th><th>Room</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($bills as $b): ?>
                    <tr>
                        <td class="id-tag">#<?= $b['bill_id'] ?></td>
                        <td><?= htmlspecialchars($b['patient_name']) ?></td>
                        <td>₹<?= number_format($b['treatment_cost'], 2) ?></td>
                        <td>₹<?= number_format($b['room_charges'], 2) ?></td>
                        <td><strong>₹<?= number_format($b['total_amount'], 2) ?></strong></td>
                        <td><?= pay_badge($b['payment_status']) ?></td>
                        <td><?= htmlspecialchars(substr($b['bill_date'],0,10)) ?></td>
                        <td class="action-icons">
                            <?php if ($b['payment_status'] !== 'Paid'): ?>
                                <a href="billing.php?mark_paid=<?= $b['bill_id'] ?>" title="Mark as paid">✓</a>
                            <?php endif; ?>
                            <a href="billing.php?delete=<?= $b['bill_id'] ?>" class="danger" title="Delete bill" data-confirm="Delete this bill?">🗑</a>
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
