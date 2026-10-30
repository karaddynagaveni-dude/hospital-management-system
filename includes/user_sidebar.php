<?php
$active = $active ?? '';
function unav_class($key, $active) {
    return 'nav-link' . ($key === $active ? ' active' : '');
}
?>
<aside class="sidebar">
    <div class="logo">Hospital MS<small>PATIENT PORTAL</small></div>

    <a href="dashboard.php" class="<?= unav_class('dashboard', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        My Overview
    </a>
    <a href="book_appointment.php" class="<?= unav_class('book', $active) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
        Book Appointment
    </a>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
        </a>
    </div>
</aside>
