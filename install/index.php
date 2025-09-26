<?php
/**
 * Installer Controller
 *
 * This script manages the multi-stage installation process.
 */
session_start();

// Define the valid stages of the installer.
$stages = [
    'welcome',
    'database',
    'admin',
    'complete'
];

// Determine the current stage from the URL, defaulting to 'welcome'.
$current_stage = isset($_GET['stage']) && in_array($_GET['stage'], $stages) ? $_GET['stage'] : 'welcome';

// If the config file already exists, the script is installed.
// Redirect to the main site to prevent re-installation.
if (file_exists('../includes/config.php')) {
    header('Location: ../index.php');
    exit;
}

// Include the PHP file for the current stage.
$stage_file = "stages/{$current_stage}.php";
if (file_exists($stage_file)) {
    require_once $stage_file;
} else {
    die("Error: Installer stage '{$current_stage}' could not be found.");
}
?>