<?php
// We need to start the session on all admin pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an admin. If not, redirect them to the homepage.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    // Adjust the path to the root index.php from the admin/includes directory
    header("location: ../../index.php");
    exit;
}

// Fetch site name for the header
require_once __DIR__ . '/../../includes/db_connect.php';
$settings_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key = 'site_name'";
$result = $mysqli->query($settings_sql);
$settings = $result->fetch_assoc();
$site_name = $settings['setting_value'] ?? 'Eflex';

// Determine the active page to highlight the nav link
$active_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($site_name); ?></title>
    <!-- Bootstrap CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom Admin CSS -->
    <link href="css/admin_style.css" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <h3><a href="dashboard.php" class="text-white text-decoration-none"><?php echo htmlspecialchars($site_name); ?> Admin</a></h3>
        </div>

        <ul class="list-unstyled components">
            <li class="<?php echo ($active_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($active_page == 'manage_products.php' || $active_page == 'add_product.php' || $active_page == 'edit_product.php') ? 'active' : ''; ?>">
                <a href="manage_products.php"><i class="fas fa-box"></i> Products</a>
            </li>
            <li class="<?php echo ($active_page == 'manage_categories.php') ? 'active' : ''; ?>">
                <a href="manage_categories.php"><i class="fas fa-list"></i> Categories</a>
            </li>
             <li class="<?php echo ($active_page == 'manage_banners.php') ? 'active' : ''; ?>">
                <a href="manage_banners.php"><i class="fas fa-images"></i> Banners</a>
            </li>
            <li class="<?php echo ($active_page == 'manage_hero.php') ? 'active' : ''; ?>">
                <a href="manage_hero.php"><i class="fas fa-film"></i> Hero Slider</a>
            </li>
            <li class="<?php echo ($active_page == 'manage_orders.php' || $active_page == 'order_detail.php') ? 'active' : ''; ?>">
                <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            </li>
            <li class="<?php echo ($active_page == 'manage_users.php' || $active_page == 'edit_user.php') ? 'active' : ''; ?>">
                <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li class="<?php echo ($active_page == 'site_settings.php') ? 'active' : ''; ?>">
                <a href="site_settings.php"><i class="fas fa-cog"></i> Site Settings</a>
            </li>
        </ul>
        <ul class="list-unstyled CTAs">
             <li><a href="../index.php" class="btn btn-info w-100 text-white" target="_blank">View Live Site</a></li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div class="admin-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
