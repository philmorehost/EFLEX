<?php
// config/config.php

// --- Database Credentials ---
// These will be used by database.php to connect to the database.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cbt_platform');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default password for XAMPP/WAMP is empty
define('DB_CHARSET', 'utf8mb4');

// --- Application Settings ---

// Base URL of the application (e.g., http://localhost/cbt-platform)
// This helps in creating absolute links and redirecting users.
// Using `isset` to avoid errors if run from the command line (CLI).
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
// Calculates the base path of the project dynamically.
// This assumes the public directory is the web root, so we go one level up from the script's directory.
$script_name = dirname($_SERVER['SCRIPT_NAME']);
// If the script is in the root, dirname might return '.', so we handle that.
$base_path = ($script_name === '.' || $script_name === '/') ? '' : $script_name;
define('BASE_URL', $protocol . $host . rtrim($base_path, '/'));


// Root path of the application for file includes.
// dirname(__DIR__) gets the parent directory of the current file's directory (which is 'config'),
// so it points to the project root.
define('ROOT_PATH', dirname(__DIR__));

// --- Other Settings ---

// Set the default timezone to avoid potential date/time issues.
date_default_timezone_set('UTC');

// Enable or disable error reporting for development vs. production.
// In a real-world application, this would be controlled by an environment variable.
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
?>
