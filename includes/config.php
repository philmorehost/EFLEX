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
// The base URL of the application.
// Dynamically determine the base URL to make the application more portable.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Assumes the project is in the root directory of a domain or subdomain.
// For subdirectory installs, this might need adjustment e.g. $host . '/subdirectory/';
define('BASE_URL', $protocol . $host . '/');

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
