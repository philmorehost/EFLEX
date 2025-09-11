<?php
// =================================================================
// CBT PLATFORM CONFIGURATION FILE
// =================================================================

// --- PHP Session Start ---
// Must be called at the beginning of any script that uses sessions.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Error Reporting ---
// For development: show all errors. For production, log errors instead.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database Credentials ---
// Replace with your actual database details.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cbt_platform');

// --- Site Configuration ---
// The name of the application.
define('SITE_NAME', 'CBT Platform');
// The base URL of the application. MUST end with a forward slash (/).
define('BASE_URL', 'http://localhost/cbt/'); // Adjust this to your project's root URL

// --- Create Database Connection using MySQLi ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Check Connection ---
// If the connection fails, display an error message and exit.
if ($conn->connect_error) {
    // In a production environment, you would log this error and show a user-friendly message.
    die("Database Connection Failed: " . $conn->connect_error);
}

// --- Set Character Set ---
// It's crucial to set the character set to utf8mb4 for full Unicode support.
if (!$conn->set_charset("utf8mb4")) {
    // In a production environment, log this error.
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

?>
