<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an admin. If not, redirect them to the homepage.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    // A little security through obscurity. If they aren't an admin, just send them to the homepage.
    // They don't need to know an admin section exists.
    header("location: ../index.php");
    exit;
}

// Include the database connection and helper functions
// The path is relative to the admin folder, so we go up one level.
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Base path for assets
$base_url = "../";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Eflex</title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo $base_url; ?>css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom Admin CSS -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .main-content {
            flex: 1;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            z-index: 1020;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: #495057;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .ck-editor__editable_inline {
            min-height: 250px;
        }
        #sidebar-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1031;
            width: 32px;
            height: 28px;
            background: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            display: none; /* Hidden by default */
            flex-direction: column;
            justify-content: space-around;
            padding: 5px;
        }
        #sidebar-toggle span {
            display: block;
            width: 100%;
            height: 2px;
            background-color: #343a40;
            border-radius: 2px;
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        #sidebar-toggle.is-active span:nth-of-type(1) {
            transform: translateY(6px) rotate(45deg);
        }
        #sidebar-toggle.is-active span:nth-of-type(2) {
            opacity: 0;
        }
        #sidebar-toggle.is-active span:nth-of-type(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                left: -250px;
                transition: left 0.3s ease-in-out;
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            body.sidebar-toggled .sidebar {
                left: 0;
            }
            #sidebar-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body id="admin-body">

<button class="d-lg-none" id="sidebar-toggle" aria-label="Toggle sidebar">
    <span></span>
    <span></span>
    <span></span>
</button>

<div class="sidebar">
    <h3 class="text-center">Eflex Admin</h3>
    <hr style="background-color: #fff;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_products.php"><i class="fas fa-box"></i> Manage Packages</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_categories.php"><i class="fas fa-tags"></i> Manage Categories</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_orders.php"><i class="fas fa-receipt"></i> Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_subscriptions.php"><i class="fas fa-id-card"></i> Manage Subscriptions</a>
        </li>
         <li class="nav-item">
            <a class="nav-link" href="site_settings.php"><i class="fas fa-cog"></i> Site Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_drive.php"><i class="fab fa-google-drive"></i> Manage Drive</a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-home"></i> View Site</a>
            <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="content-wrapper">
    <div class="container-fluid">
