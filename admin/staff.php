<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/db.php';
$active = 'staff';

$error = '';
$editStaff = null;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM staff WHERE staff_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: staff.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $role         = trim($_POST['role'] ?? '');
    $department   = trim($_POST['department'] ?? '');
    $contact_no   = trim($_POST['contact_no'] ?? '');
    $shift_timing = trim($_POST['shift_timing'] ?? '');
    $staff_id     = $_POST['staff_id'] ?? '';

    if ($name === '') {
        $error = 'Staff name is required.';
    } elseif ($staff_id !== '') {
        $stmt = $pdo->prepare("UPDATE staff SET name=?, role=?, department=?, contact_no=?, shift_timing=? WHERE staff_id=?");
        $stmt->execute([$name, $role, $department, $contact_no, $shift_timing, $staff_id]);
        header('Location: staff.php?updated=1');
        exit;
    } else {
        $stmt = $pdo->prepare("INSERT INTO staff (name, role, department, contact_no, shift_timing) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $role, $department, $contact_no, $shift_timing]);
        header('Location: staff.php?added=1');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editStaff = $stmt->fetch();
}

$staffList = $pdo->query("SELECT * FROM staff ORDER BY staff_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff - Hospital Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <button class="mobile-toggle" aria-label="Toggle menu">☰</button>
            <div>
                <h1>Staff Management</h1>
                <p class="welcome">Manage non-clinical and support staff records.</p>
            </div>
        </div>

        <?php if (isset($_GET['added'])): ?><div class="alert alert-success">Staff member added.</div><?php endif; ?>
        <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Staff details updated.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Staff member removed.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="panel">
            <div class="panel-head">
                <h2><?= $editStaff ? 'Edit Staff Member' : 'Add New Staff Member' ?></h2>
                <?php if ($editStaff): ?><a href="staff.php" class="btn btn-outline btn-sm">Cancel edit</a><?php endif; ?>
            </div>
            <form method="POST" data-validate novalidate>
                <?php if ($editStaff): ?><input type="hidden" name="staff_id" value="<?= $editStaff['staff_id'] ?>"><?php endif; ?>
                <div class="form-inline-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($editStaff['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" name="role" placeholder="e.g. Nurse, Receptionist" value="<?= htmlspecialchars($editStaff['role'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" value="<?= htmlspecialchars($editStaff['department'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact No.</label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($editStaff['contact_no'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Shift Timing</label>
                        <input type="text" name="shift_timing" placeholder="e.g. 9 AM - 5 PM" value="<?= htmlspecialchars($editStaff['shift_timing'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><?= $editStaff ? 'Save Changes' : 'Add Staff' ?></button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>All Staff (<?= count($staffList) ?>)</h2></div>
            <?php if (empty($staffList)): ?>
                <div class="empty-state">No staff members added yet.</div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Department</th><th>Contact</th><th>Shift</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($staffList as $s): ?>
                    <tr>
                        <td class="id-tag">#<?= $s['staff_id'] ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['role'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['department'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['contact_no'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['shift_timing'] ?: '—') ?></td>
                        <td class="action-icons">
                            <a href="staff.php?edit=<?= $s['staff_id'] ?>" title="Edit">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                            </a>
                            <a href="staff.php?delete=<?= $s['staff_id'] ?>" class="danger" title="Delete" data-confirm="Remove this staff member?">
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
