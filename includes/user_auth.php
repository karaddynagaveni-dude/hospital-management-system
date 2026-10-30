<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit;
}
