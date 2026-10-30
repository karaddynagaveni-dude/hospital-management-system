<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'doctors';

$error = '';
$editDoctor = null;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: doctors.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $contact_no     = trim($_POST['contact_no'] ?? '');
    $department     = trim($_POST['department'] ?? '');
    $availability   = $_POST['availability'] ?? 'Available';
    $doctor_id      = $_POST['doctor_id'] ?? '';

    if ($name === '') {
        $error = 'Doctor name is required.';
    } elseif ($doctor_id !== '') {
        $stmt = $pdo->prepare("UPDATE doctors SET name=?, specialization=?, contact_no=?, department=?, availability=? WHERE doctor_id=?");
        $stmt->execute([$name, $specialization, $contact_no, $department, $availability, $doctor_id]);
        header('Location: doctors.php?updated=1');
        exit;
    } else {
        $stmt = $pdo->prepare("INSERT INTO doctors (name, specialization, contact_no, department, availability) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $specialization, $contact_no, $department, $availability]);
        header('Location: doctors.php?added=1');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editDoctor = $stmt->fetch();
}

$doctors = $pdo->query("SELECT * FROM doctors ORDER BY doctor_id DESC")->fetchAll();

function avail_badge($status) {
    $map = ['Available' => 'badge-green', 'On Leave' => 'badge-amber', 'Busy' => 'badge-coral'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-gray') . '">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctors - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Doctor Management</h1>
                <p class="welcome">Add doctors and track department availability.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Doctor added.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Doctor details updated.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Doctor removed.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="panel">
            <div class="panel-head">
                <h2><?= $editDoctor ? 'Edit Doctor' : 'Add New Doctor' ?></h2>
                <?php if ($editDoctor): ?><a href="doctors.php" class="btn btn-outline btn-sm">Cancel edit</a><?php endif; ?>
            </div>
            <form method="POST" data-validate novalidate>
                <?php if ($editDoctor): ?><input type="hidden" name="doctor_id" value="<?= $editDoctor['doctor_id'] ?>"><?php endif; ?>
                <div class="form-inline-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($editDoctor['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" value="<?= htmlspecialchars($editDoctor['specialization'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" value="<?= htmlspecialchars($editDoctor['department'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact No.</label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($editDoctor['contact_no'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Availability</label>
                        <select name="availability">
                            <?php foreach (['Available','On Leave','Busy'] as $s): ?>
                                <option value="<?= $s ?>" <?= (($editDoctor['availability'] ?? 'Available') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><?= $editDoctor ? 'Save Changes' : 'Add Doctor' ?></button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>All Doctors (<?= count($doctors) ?>)</h2></div>
            <?php if (empty($doctors)): ?>
                <div class="empty-state">No doctors added yet.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Specialization</th><th>Department</th><th>Contact</th><th>Availability</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($doctors as $d): ?>
                    <tr>
                        <td class="id-tag">#<?= $d['doctor_id'] ?></td>
                        <td>Dr. <?= htmlspecialchars($d['name']) ?></td>
                        <td><?= htmlspecialchars($d['specialization'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($d['department'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($d['contact_no'] ?: '—') ?></td>
                        <td><?= avail_badge($d['availability']) ?></td>
                        <td class="action-icons">
                            <a href="doctors.php?edit=<?= $d['doctor_id'] ?>" title="Edit">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                            </a>
                            <a href="doctors.php?delete=<?= $d['doctor_id'] ?>" class="danger" title="Delete" data-confirm="Remove this doctor?">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                            </a>
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
