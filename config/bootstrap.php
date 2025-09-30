<?php
// config/bootstrap.php - Application initializer

// --- Core Path Definitions ---
// Define App Root first
define('APP_ROOT', dirname(__DIR__));

// Define Base URL for redirects and asset links
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Get the script name and remove the file name to get the directory
    $script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    // Ensure it ends with a slash
    $base_path = rtrim($script_dir, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Start the session
session_start();

// --- Load Configuration & Installation Check ---
// This is the central check. Any file including this bootstrap will be protected.
if (file_exists(APP_ROOT . '/config/config.php')) {
    require_once APP_ROOT . '/config/config.php';
} else {
    // If the config file doesn't exist, redirect to the installer, unless we are already in the installer.
    if (strpos($_SERVER['REQUEST_URI'], '/installer/') === false) {
        header('Location: ' . BASE_URL . 'installer/index.php');
        exit();
    }
}

// --- Autoloader ---
// Automatically loads classes so we don't have to use `require` everywhere.
spl_autoload_register(function ($className) {
    // Class names are expected to be in the format: Namespace\ClassName
    // e.g., Core\Router will map to app/core/Router.php
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// --- Load Helpers ---
// Load all helper files from the app/helpers directory
foreach (glob(APP_ROOT . '/app/helpers/*.php') as $filename) {
    require_once $filename;
}