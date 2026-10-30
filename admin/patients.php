<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'patients';

$message = '';
$error = '';
$editPatient = null;

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: patients.php?deleted=1');
    exit;
}

// ---- Handle Add / Update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $age        = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
    $gender     = $_POST['gender'] ?? null;
    $address    = trim($_POST['address'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $disease    = trim($_POST['disease'] ?? '');
    $admission_date = $_POST['admission_date'] ?: null;
    $discharge_date = $_POST['discharge_date'] ?: null;
    $patient_id = $_POST['patient_id'] ?? '';

    if ($name === '') {
        $error = 'Patient name is required.';
    } elseif ($patient_id !== '') {
        // Update existing patient (record fields only, not login)
        $stmt = $pdo->prepare("UPDATE patients SET name=?, age=?, gender=?, address=?, contact_no=?, disease=?, admission_date=?, discharge_date=? WHERE patient_id=?");
        $stmt->execute([$name, $age, $gender, $address, $contact_no, $disease, $admission_date, $discharge_date, $patient_id]);
        header('Location: patients.php?updated=1');
        exit;
    } else {
        // Add new patient with a generated login
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $error = 'Username and password are required for a new patient.';
        } else {
            $check = $pdo->prepare("SELECT patient_id FROM patients WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetch()) {
                $error = 'That username is already taken.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO patients (username, password, name, age, gender, address, contact_no, disease, admission_date, discharge_date) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$username, $hashed, $name, $age, $gender, $address, $contact_no, $disease, $admission_date, $discharge_date]);
                header('Location: patients.php?added=1');
                exit;
            }
        }
    }
}

// ---- Handle Edit (load record) ----
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editPatient = $stmt->fetch();
}

$patients = $pdo->query("SELECT * FROM patients ORDER BY patient_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Patient Records</h1>
                <p class="welcome">Register, update and review patient information.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Patient added successfully.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Patient details updated.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Patient record deleted.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="panel">
            <div class="panel-head">
                <h2><?= $editPatient ? 'Edit Patient' : 'Register New Patient' ?></h2>
                <?php if ($editPatient): ?><a href="patients.php" class="btn btn-outline btn-sm">Cancel edit</a><?php endif; ?>
            </div>

            <form method="POST" data-validate novalidate>
                <?php if ($editPatient): ?>
                    <input type="hidden" name="patient_id" value="<?= $editPatient['patient_id'] ?>">
                <?php endif; ?>

                <div class="form-inline-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($editPatient['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" min="0" max="130" value="<?= htmlspecialchars($editPatient['age'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">-- Select --</option>
                            <?php foreach (['Male','Female','Other'] as $g): ?>
                                <option value="<?= $g ?>" <?= (($editPatient['gender'] ?? '') === $g) ? 'selected' : '' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact No.</label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($editPatient['contact_no'] ?? '') ?>">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($editPatient['address'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Disease / Diagnosis</label>
                        <input type="text" name="disease" value="<?= htmlspecialchars($editPatient['disease'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Admission Date</label>
                        <input type="date" name="admission_date" value="<?= htmlspecialchars($editPatient['admission_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Discharge Date</label>
                        <input type="date" name="discharge_date" value="<?= htmlspecialchars($editPatient['discharge_date'] ?? '') ?>">
                    </div>

                    <?php if (!$editPatient): ?>
                    <div class="form-group">
                        <label>Login Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Login Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-sm"><?= $editPatient ? 'Save Changes' : 'Add Patient' ?></button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>All Patients (<?= count($patients) ?>)</h2></div>

            <?php if (empty($patients)): ?>
                <div class="empty-state">No patients registered yet. Use the form above to add one.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Contact</th><th>Disease</th><th>Admitted</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td class="id-tag">#<?= $p['patient_id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['age'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['gender'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['contact_no'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['disease'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($p['admission_date'] ?? '—') ?></td>
                        <td class="action-icons">
                            <a href="patients.php?edit=<?= $p['patient_id'] ?>" title="Edit">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                            </a>
                            <a href="patients.php?delete=<?= $p['patient_id'] ?>" class="danger" title="Delete" data-confirm="Delete this patient record? This also removes their appointments and bills.">
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
