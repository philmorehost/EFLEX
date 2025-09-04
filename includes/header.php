<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Initial Setup Checks ---

// 1. Check and create upload directories
// Use absolute paths based on this file's location to avoid relative path issues.
$base_path = __DIR__ . '/../';
$uploads_dir = $base_path . 'uploads';
$proofs_dir = $uploads_dir . '/payment_proofs';

if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}
if (!is_dir($proofs_dir)) {
    mkdir($proofs_dir, 0777, true);
}

// 2. Include and run database setup/migration
require_once 'db_connect.php';
require_once 'helpers.php';
load_app_settings(); // Explicitly load settings

// Fetch categories for the navigation dropdown
$category_sql = "SELECT * FROM categories ORDER BY name ASC";
$category_result = $mysqli->query($category_sql);
$nav_categories = $category_result->fetch_all(MYSQLI_ASSOC);

// Calculate cart item count
$cart_item_count = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])){
    $cart_item_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eflex E-commerce</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/custom_style.css" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="header-main">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-logo">
                    <a class="navbar-brand" href="index.php">Eflex</a>
                </div>
                <div class="header-search">
                    <form class="d-flex" action="search.php" method="get">
                        <input class="form-control me-2" type="search" name="query" placeholder="Search Products" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
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
                <li><a href="subscriptions.php" class="nav-link">Subscriptions</a></li>
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
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li><a href="lesson_notes.php" class="nav-link">Lesson Notes</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'): ?>
                    <li><a href="admin/dashboard.php" class="nav-link">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<div class="container mt-4">
