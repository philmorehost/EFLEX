<?php
// This file will handle the database connection.
// It requires the config.php file which is created during installation.

if (!file_exists(__DIR__ . '/config.php')) {
    // This should not happen if the installer was run correctly.
    // But as a fallback, we can redirect to the installer.
    header('Location: ../install/index.php');
    exit;
}

require_once 'config.php';

// Create a new MySQLi object
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    // In a real application, you'd want to log this error, not display it
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Set the character set to utf8mb4 for full Unicode support
$mysqli->set_charset('utf8mb4');
?>