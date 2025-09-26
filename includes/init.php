<?php
// Core App Initializer

// Start the session if it's not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the root path of the application for robust includes.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/..');
}

// Include core files using absolute paths.
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';
?>