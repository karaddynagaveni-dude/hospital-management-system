<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'beds';

$error = '';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM beds WHERE bed_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: beds.php?deleted=1');
    exit;
}

// Discharge a bed (free it up)
if (isset($_GET['discharge'])) {
    $stmt = $pdo->prepare("UPDATE beds SET status='Available', patient_id=NULL WHERE bed_id=?");
    $stmt->execute([(int)$_GET['discharge']]);
    header('Location: beds.php?freed=1');
    exit;
}

// Add a new bed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ward_type']) && !isset($_POST['assign_bed_id'])) {
    $ward_type = trim($_POST['ward_type']);
    if ($ward_type === '') {
        $error = 'Ward type is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO beds (ward_type, status) VALUES (?, 'Available')");
        $stmt->execute([$ward_type]);
        header('Location: beds.php?added=1');
        exit;
    }
}

// Assign a patient to an available bed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_bed_id'])) {
    $bed_id = $_POST['assign_bed_id'];
    $patient_id = $_POST['assign_patient_id'] ?? '';
    if (!$patient_id) {
        $error = 'Select a patient to allocate this bed.';
    } else {
        $stmt = $pdo->prepare("UPDATE beds SET status='Occupied', patient_id=? WHERE bed_id=?");
        $stmt->execute([$patient_id, $bed_id]);
        header('Location: beds.php?assigned=1');
        exit;
    }
}

$beds = $pdo->query("
    SELECT b.*, p.name AS patient_name
    FROM beds b
    LEFT JOIN patients p ON p.patient_id = b.patient_id
    ORDER BY b.ward_type, b.bed_id
")->fetchAll();

$patients = $pdo->query("SELECT patient_id, name FROM patients ORDER BY name")->fetchAll();

$totalBeds = count($beds);
$freeBeds  = count(array_filter($beds, fn($b) => $b['status'] === 'Available'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beds &amp; Wards - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Bed &amp; Ward Allocation</h1>
                <p class="welcome"><?= $freeBeds ?> of <?= $totalBeds ?> beds currently available.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Bed added.</div><?php endif; ?>
        <?php if (isset($_GET['assigned'])): ?><div class="alert alert-success">Bed allocated to patient.</div><?php endif; ?>
        <?php if (isset($_GET['freed'])): ?><div class="alert alert-success">Bed marked available.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Bed removed.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="two-col">
            <div class="panel">
                <div class="panel-head"><h2>All Beds (<?= $totalBeds ?>)</h2></div>
                <?php if (empty($beds)): ?>
                    <div class="empty-state">No beds set up yet.</div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>ID</th><th>Ward</th><th>Status</th><th>Patient</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($beds as $b): ?>
                        <tr>
                            <td class="id-tag">#<?= $b['bed_id'] ?></td>
                            <td><?= htmlspecialchars($b['ward_type']) ?></td>
                            <td><span class="badge <?= $b['status'] === 'Available' ? 'badge-green' : 'badge-coral' ?>"><?= $b['status'] ?></span></td>
                            <td><?= $b['patient_name'] ? htmlspecialchars($b['patient_name']) : '—' ?></td>
                            <td class="action-icons">
                                <?php if ($b['status'] === 'Occupied'): ?>
                                    <a href="beds.php?discharge=<?= $b['bed_id'] ?>" title="Free this bed" data-confirm="Mark this bed as available again?">Free</a>
                                <?php endif; ?>
                                <a href="beds.php?delete=<?= $b['bed_id'] ?>" class="danger" title="Remove bed" data-confirm="Remove this bed permanently?">🗑</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="panel">
                    <div class="panel-head"><h2>Add Bed</h2></div>
                    <form method="POST" data-validate novalidate>
                        <div class="form-group">
                            <label>Ward Type</label>
                            <input type="text" name="ward_type" placeholder="e.g. ICU, General Ward" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Add Bed</button>
                    </form>
                </div>

                <div class="panel">
                    <div class="panel-head"><h2>Allocate Bed</h2></div>
                    <?php
                        $availableBeds = array_filter($beds, fn($b) => $b['status'] === 'Available');
                    ?>
                    <?php if (empty($availableBeds) || empty($patients)): ?>
                        <div class="empty-state">No available beds or patients to allocate right now.</div>
                    <?php else: ?>
                    <form method="POST" data-validate novalidate>
                        <div class="form-group">
                            <label>Available Bed</label>
                            <select name="assign_bed_id" required>
                                <?php foreach ($availableBeds as $b): ?>
                                    <option value="<?= $b['bed_id'] ?>">#<?= $b['bed_id'] ?> — <?= htmlspecialchars($b['ward_type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Patient</label>
                            <select name="assign_patient_id" required>
                                <option value="">-- Select patient --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['patient_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Allocate Bed</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="../js/script.js"></script>
</body>
</html>
