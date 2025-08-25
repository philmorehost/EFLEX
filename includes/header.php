<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Initial Setup Checks ---
$base_path = __DIR__ . '/../';
$uploads_dir = $base_path . 'uploads';
$pwa_dir = $uploads_dir . '/pwa';
$proofs_dir = $uploads_dir . '/payment_proofs';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0777, true);
if (!is_dir($pwa_dir)) mkdir($pwa_dir, 0777, true);
if (!is_dir($proofs_dir)) mkdir($proofs_dir, 0777, true);

// Include and run database setup/migration
require_once 'db_connect.php';

// --- Fetch all site settings ---
$settings_sql = "SELECT setting_key, setting_value FROM settings";
$result = $mysqli->query($settings_sql);
$settings = [];
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}
// Define variables for easier access, with defaults
$site_name = $settings['site_name'] ?? 'Eflex';
$site_logo = $settings['site_logo'] ?? '';
$meta_title = $settings['meta_title'] ?? $site_name;
$meta_description = $settings['meta_description'] ?? 'An e-commerce website.';
$meta_keywords = $settings['meta_keywords'] ?? 'e-commerce, shop, online store';
$pwa_enabled = !empty($settings['pwa_enabled']);
$theme_primary_color = $settings['theme_primary_color'] ?? '#ae8e6a';
$theme_secondary_color = $settings['theme_secondary_color'] ?? '#f2f2f2';
$_SESSION['currency_symbol'] = $settings['currency_symbol'] ?? '$';
$_SESSION['currency_code'] = $settings['currency_code'] ?? 'USD';


// --- Fetch categories for the navigation dropdown ---
$category_sql = "SELECT * FROM categories ORDER BY name ASC";
$category_result = $mysqli->query($category_sql);
$nav_categories = $category_result->fetch_all(MYSQLI_ASSOC);

// --- Calculate cart item count ---
$cart_item_count = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])){
    $cart_item_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $settings['language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">

    <?php if($pwa_enabled): ?>
    <link rel="manifest" href="/manifest.php">
    <meta name="theme-color" content="<?php echo htmlspecialchars($settings['pwa_theme_color'] ?? '#ffffff'); ?>">
    <?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/custom_style.css" rel="stylesheet">

    <!-- Dynamic Theme Colors -->
    <style>
        :root {
            --woodmart-primary-color: <?php echo htmlspecialchars($theme_primary_color); ?>;
            --woodmart-light-gray: <?php echo htmlspecialchars($theme_secondary_color); ?>;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="header-main">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-logo">
                    <a class="navbar-brand" href="index.php">
                        <?php if(!empty($site_logo)): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" style="max-height: 50px;">
                        <?php else: ?>
                            <?php echo htmlspecialchars($site_name); ?>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="header-search">
                    <form class="d-flex" action="search.php" method="get">
                        <input class="form-control me-2" type="search" name="query" placeholder="Search Products" aria-label="Search">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="header-actions">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="account.php" class="header-action-btn"><i class="fas fa-user"></i><span><?php echo htmlspecialchars($_SESSION["username"]); ?></span></a>
                        <a href="logout.php" class="header-action-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                    <?php else: ?>
                        <a href="login.php" class="header-action-btn"><i class="fas fa-user"></i><span>Login</span></a>
                    <?php endif; ?>
                    <a href="cart.php" class="header-action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge rounded-pill bg-primary cart-badge"><?php echo $cart_item_count; ?></span>
                        <span>Cart</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <nav class="header-nav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item-dropdown">
                    <a href="products.php" class="nav-link">Products <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu-custom">
                        <li><a href="products.php">All Products</a></li>
                        <?php if(count($nav_categories) > 0): ?>
                             <li class="divider"></li>
                            <?php foreach ($nav_categories as $nav_category): ?>
                                <li><a href="category.php?id=<?php echo $nav_category['id']; ?>"><?php echo htmlspecialchars($nav_category['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php if(isset($_SESSION["role_id"])): ?>
                    <li><a href="admin/dashboard.php" class="nav-link">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<div class="container mt-4">
