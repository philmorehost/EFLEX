<?php
// Installer controller

// Check if the application is already installed
if (file_exists('../install.lock')) {
    die("Installer is locked. To protect your site, please delete the 'installer' directory from your server. If you need to reinstall, you must first delete the 'install.lock' file from your root directory.");
}

// Simple step management
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Define the template for the current step
switch ($step) {
    case 1:
        $template = "templates/step1_welcome.php";
        break;
    case 2:
        $template = "templates/step2_database.php";
        break;
    case 3:
        $template = "templates/step3_settings.php";
        break;
    case 4:
        $template = "templates/step4_finish.php";
        break;
    default:
        $template = "templates/step1_welcome.php";
        break;
}

// Check if the template file exists before including it
if (!file_exists($template)) {
    die("Error: Installer template not found.");
}

// Include the main layout
include 'templates/layout.php';