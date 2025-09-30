<?php
// config/bootstrap.php - Application initializer

// Start the session
session_start();

// --- Load Configuration ---
// The config file is expected to be present after installation.
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    // If config is missing and not in installer, die.
    if (!file_exists('../installer/index.php')) {
        die('<h1>Configuration Error</h1><p>The configuration file is missing and the installer is not available. Please re-upload all files.</p>');
    }
    // If installer exists, redirect there.
    header('Location: ../installer/index.php');
    exit();
}


// --- Autoloader ---
// Automatically loads classes so we don't have to use `require` everywhere.
spl_autoload_register(function ($className) {
    // Core libraries are in 'app/core', others in 'app/controllers', 'app/models', etc.
    // Class names are expected to be in the format: Namespace\ClassName
    // e.g., Core\Router will map to app/core/Router.php
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', strtolower($className)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// --- Load Helpers ---
// Load all helper files from the app/helpers directory
foreach (glob(dirname(__DIR__) . '/app/helpers/*.php') as $filename) {
    require_once $filename;
}

// Define base URL if not defined in config
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Assumes the app is in the root. Adjust if it's in a subdirectory.
    define('BASE_URL', $protocol . $host);
}

// Define App Root
define('APP_ROOT', dirname(__DIR__));