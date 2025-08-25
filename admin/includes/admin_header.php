<?php
// We need to start the session on all admin pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic login check. Specific permissions are checked on each page.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../../login.php");
    exit;
}

// Include database and auth check
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth_check.php';


// Fetch site name and OneSignal App ID for the header
$settings_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'onesignal_app_id')";
$result = $mysqli->query($settings_sql);
$settings = [];
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_name = $settings['site_name'] ?? 'Eflex';
$oneSignalAppId = $settings['onesignal_app_id'] ?? '';

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
    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
    <script>
      var oneSignalAppId = "<?php echo $oneSignalAppId; ?>";
    </script>
    <script src="js/pwa-init.js"></script>
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
            <?php if(has_permission('manage_products')): ?><li class="<?php echo ($active_page == 'manage_products.php' || $active_page == 'add_product.php' || $active_page == 'edit_product.php') ? 'active' : ''; ?>">
                <a href="manage_products.php"><i class="fas fa-box"></i> Products</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_categories')): ?><li class="<?php echo ($active_page == 'manage_categories.php') ? 'active' : ''; ?>">
                <a href="manage_categories.php"><i class="fas fa-list"></i> Categories</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_banners')): ?><li class="<?php echo ($active_page == 'manage_banners.php') ? 'active' : ''; ?>">
                <a href="manage_banners.php"><i class="fas fa-images"></i> Banners</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_hero_slider')): ?><li class="<?php echo ($active_page == 'manage_hero.php') ? 'active' : ''; ?>">
                <a href="manage_hero.php"><i class="fas fa-film"></i> Hero Slider</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_orders')): ?><li class="<?php echo ($active_page == 'manage_orders.php' || $active_page == 'order_detail.php') ? 'active' : ''; ?>">
                <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_roles')): ?><li class="<?php echo ($active_page == 'manage_staff.php') ? 'active' : ''; ?>">
                <a href="manage_staff.php"><i class="fas fa-user-tie"></i> Staff</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_roles')): ?><li class="<?php echo ($active_page == 'manage_roles.php') ? 'active' : ''; ?>">
                <a href="manage_roles.php"><i class="fas fa-user-shield"></i> Roles & Permissions</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_users')): ?><li class="<?php echo ($active_page == 'manage_users.php' || $active_page == 'edit_user.php') ? 'active' : ''; ?>">
                <a href="manage_users.php"><i class="fas fa-users"></i> Customers</a>
            </li><?php endif; ?>
            <?php if(has_permission('manage_site_settings')): ?><li class="<?php echo ($active_page == 'site_settings.php') ? 'active' : ''; ?>">
                <a href="site_settings.php"><i class="fas fa-cog"></i> Site Settings</a>
            </li><?php endif; ?>
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
