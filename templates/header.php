<?php
// templates/header.php

// This is a basic check to ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_role_id = $_SESSION['user_role_id'] ?? null;

// Fetch the user's role name if not already in session
if (!isset($_SESSION['user_role']) && $user_role_id) {
    $pdo = require __DIR__ . '/../config/database.php';
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$user_role_id]);
    $_SESSION['user_role'] = $stmt->fetchColumn();
}
$user_role = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Platform Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 280px; }
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100%; width: var(--sidebar-width); background-color: #343a40; color: white; padding-top: 1rem; transition: margin-left 0.3s; z-index: 1030;}
        .main-content { margin-left: var(--sidebar-width); padding: 20px; transition: margin-left 0.3s; }
        .sidebar .nav-link { color: #adb5bd; font-size: 1.05rem; padding: .75rem 1.5rem;}
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #495057; }
        .sidebar .nav-link .fa-fw { width: 1.25em; margin-right: 0.5rem; }
        .sidebar-header { padding: 0 1.5rem 1rem 1.5rem; border-bottom: 1px solid #495057; margin-bottom: 1rem; }
        .sidebar-heading { padding: 0 1.5rem; font-size: .8rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05rem; }
        .top-navbar { margin-left: var(--sidebar-width); background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1); transition: margin-left 0.3s; }
        @media (max-width: 992px) {
            .sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            .main-content, .top-navbar { margin-left: 0; }
            .sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header text-center">
        <h3><a href="dashboard.php" class="text-white text-decoration-none">CBT Platform</a></h3>
    </div>
    <ul class="nav flex-column">
        <li class="sidebar-heading mt-2">Core</li>
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php"><i class="fa fa-fw fa-tachometer-alt"></i>Dashboard</a>
        </li>

        <?php if ($user_role === 'Super Admin' || $user_role === 'Admin' || $user_role === 'Staff'): ?>
        <li class="sidebar-heading mt-3">Question Bank</li>
        <li class="nav-item">
            <a class="nav-link" href="add_question.php"><i class="fa fa-fw fa-plus-circle"></i>Add Question</a>
        </li>
        <?php endif; ?>
        <?php if ($user_role === 'Super Admin' || $user_role === 'Admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="categories.php"><i class="fa fa-fw fa-tags"></i>Categories</a>
        </li>
        <?php endif; ?>

        <?php if ($user_role === 'Super Admin'): ?>
        <li class="sidebar-heading mt-3">Administration</li>
        <li class="nav-item">
            <a class="nav-link" href="users.php"><i class="fa fa-fw fa-users"></i>User Management</a>
        </li>
        <?php endif; ?>

        <li class="sidebar-heading mt-3">User</li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-fw fa-file-alt"></i>My Tests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-fw fa-chart-bar"></i>My Results</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="fa fa-fw fa-user"></i>Profile</a>
        </li>
    </ul>
</div>

<nav class="navbar navbar-expand-lg navbar-light top-navbar">
    <div class="container-fluid">
        <button class="btn btn-primary d-lg-none" id="sidebarToggle"><i class="fa fa-bars"></i></button>
        <div class="collapse navbar-collapse"></div>
        <div class="navbar-nav">
            <span class="navbar-text me-3">
                Welcome, <?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($user_role); ?>)
            </span>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="main-content">
    <!-- Main page content starts here -->
