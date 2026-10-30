<?php
/**
 * Database connection (PDO + MySQL)
 * ----------------------------------
 * Edit the constants below to match your local MySQL / XAMPP / WAMP setup.
 * Default XAMPP values are already filled in (root / no password).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hms_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() .
        "<br>Please make sure MySQL is running and you have imported database/hms_db.sql");
}
