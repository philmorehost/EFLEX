<?php
// Include the header
include 'includes/header.php';

// Check for the admin creation flash message
if(isset($_SESSION['admin_created']) && $_SESSION['admin_created'] === true){
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Admin Account Created!</strong> A default admin account has been created.
            Username: <strong>admin</strong>, Password: <strong>password</strong>. Please change the password immediately.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['admin_created']);
}

// Fetch products for the tabs
// New Products
$new_products_sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$new_products_result = $mysqli->query($new_products_sql);
$new_products = $new_products_result->fetch_all(MYSQLI_ASSOC);

// Featured Products
$featured_products_sql = "SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 8";
$featured_products_result = $mysqli->query($featured_products_sql);
$featured_products = $featured_products_result->fetch_all(MYSQLI_ASSOC);

// Top Sellers
$top_sellers_sql = "SELECT * FROM products WHERE is_top_seller = 1 ORDER BY created_at DESC LIMIT 8";
$top_sellers_result = $mysqli->query($top_sellers_sql);
$top_sellers = $top_sellers_result->fetch_all(MYSQLI_ASSOC);

?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1>Welcome to Eflex</h1>
        <p class="lead">Your one-stop shop for everything you need. We offer the best products at the best prices.</p>
        <a class="btn btn-primary btn-lg" href="products.php" role="button">Shop Now</a>
    </div>
</div>

<!-- Product Tabs Section -->
<div class="container my-5">
    <div class="product-tabs">
        <ul class="nav nav-tabs justify-content-center" id="productTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab" aria-controls="new" aria-selected="true">New Arrivals</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="featured-tab" data-bs-toggle="tab" data-bs-target="#featured" type="button" role="tab" aria-controls="featured" aria-selected="false">Featured</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="topsellers-tab" data-bs-toggle="tab" data-bs-target="#topsellers" type="button" role="tab" aria-controls="topsellers" aria-selected="false">Top Sellers</button>
            </li>
        </ul>
        <div class="tab-content mt-4" id="productTabContent">
            <!-- New Arrivals Pane -->
            <div class="tab-pane fade show active" id="new" role="tabpanel" aria-labelledby="new-tab">
                <div class="row">
                    <?php foreach($new_products as $product): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <?php include 'includes/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Featured Pane -->
            <div class="tab-pane fade" id="featured" role="tabpanel" aria-labelledby="featured-tab">
                <div class="row">
                    <?php foreach($featured_products as $product): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <?php include 'includes/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Top Sellers Pane -->
            <div class="tab-pane fade" id="topsellers" role="tabpanel" aria-labelledby="topsellers-tab">
                <div class="row">
                     <?php foreach($top_sellers as $product): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                           <?php include 'includes/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- This is the old featured section. The user wants this removed and replaced by a slider. -->
<!-- For now, I will comment it out. It will be removed completely when the slider is built. -->
<!--
<h2>Featured Products</h2>
<div class="row">
    ...
</div>
-->

<!-- How It Works Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">How It Works</h2>
    <div class="row text-center">
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-search"></i></div>
                <h5>1. Browse Products</h5>
                <p>Search and Click through Products</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                <h5>2. Add to Cart</h5>
                <p>Click on ADD TO CART to Purchase</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                <h5>3. Payments</h5>
                <p>Checkout to make Payments using any of our payment Options.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                <h5>4. Shipping & Delivery</h5>
                <p>Delivery within Lagos takes 24-72hrs. Outside Lagos takes 3-5 working days.</p>
            </div>
        </div>
    </div>
</div>


<?php
// Include the footer
include 'includes/footer.php';
?>
