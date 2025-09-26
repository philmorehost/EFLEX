<?php
// Installer for the script
session_start();

// Define stages
$stages = [
    'welcome',
    'database',
    'admin',
    'complete'
];

// Get current stage from query string, default to 'welcome'
$current_stage = isset($_GET['stage']) && in_array($_GET['stage'], $stages) ? $_GET['stage'] : 'welcome';

// If config file exists, redirect to homepage to prevent re-installation
if (file_exists('../includes/config.php')) {
    header('Location: ../index.php');
    exit;
}

// Include the view for the current stage
include "stages/{$current_stage}.php";
?>