<?php
// templates/header.php

// This is a basic check to ensure user is logged in.
// A more robust solution might involve a dedicated auth-check include.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Platform Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
        }
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background-color: #343a40;
            color: white;
            padding-top: 1rem;
            transition: margin-left 0.3s;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .nav-link .fa {
            margin-right: 10px;
        }
        .sidebar-header {
            padding: 0 1rem 1rem 1rem;
            border-bottom: 1px solid #495057;
            margin-bottom: 1rem;
        }
        .sidebar-header h3 {
            margin: 0;
        }
        .top-navbar {
            margin-left: var(--sidebar-width);
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            transition: margin-left 0.3s;
        }

        /* Responsive styles for sidebar */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .main-content, .top-navbar {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>CBT Platform</h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="dashboard.php"><i class="fa fa-tachometer-alt"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-file-alt"></i>My Tests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-chart-bar"></i>My Results</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-user"></i>Profile</a>
        </li>
    </ul>
</div>

<nav class="navbar navbar-expand-lg navbar-light top-navbar">
    <div class="container-fluid">
        <button class="btn btn-primary d-lg-none" id="sidebarToggle"><i class="fa fa-bars"></i></button>
        <div class="collapse navbar-collapse"></div>
        <div class="navbar-nav">
            <span class="navbar-text me-3">
                Welcome, <?php echo htmlspecialchars($user_name); ?>
            </span>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="main-content">
    <!-- Main page content starts here -->
