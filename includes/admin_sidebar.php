<?php
// Expects $active to be set by the including page (e.g. 'dashboard', 'patients', ...)
$active = $active ?? '';

function nav_class($key, $active) {
    return 'nav-link' . ($key === $active ? ' active' : '');
}
?>
<aside class="sidebar">
    <div class="logo">Hospital MS<small>ADMIN CONSOLE</small></div>

    <a href="dashboard.php" class="<?= nav_class('dashboard', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        Dashboard
    </a>
    <a href="patients.php" class="<?= nav_class('patients', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 3.5a3.5 3.5 0 0 1 0 7"/><path d="M22 21c0-3.2-2-6-5-6.9"/></svg>
        Patients
    </a>
    <a href="doctors.php" class="<?= nav_class('doctors', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v4M16 3v4M4 9h16"/><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M12 12v5M9.5 14.5h5"/></svg>
        Doctors
    </a>
    <a href="staff.php" class="<?= nav_class('staff', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Staff
    </a>
    <a href="appointments.php" class="<?= nav_class('appointments', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
        Appointments
    </a>
    <a href="beds.php" class="<?= nav_class('beds', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20v-7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v7M2 20h20M4 11V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v5"/></svg>
        Beds &amp; Wards
    </a>
    <a href="billing.php" class="<?= nav_class('billing', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg>
        Billing
    </a>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
        </a>
    </div>
</aside>
