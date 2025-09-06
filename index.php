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

// Fetch products for the carousels
// Freemium products are those with a price of 0
$freemium_products_sql = "SELECT * FROM products WHERE price <= 0 ORDER BY created_at DESC LIMIT 8";
$freemium_products_result = $mysqli->query($freemium_products_sql);
$freemium_products = $freemium_products_result->fetch_all(MYSQLI_ASSOC);

// Premium products are featured and have a price > 0
$premium_products_sql = "SELECT * FROM products WHERE is_featured = 1 AND price > 0 ORDER BY created_at DESC LIMIT 8";
$premium_products_result = $mysqli->query($premium_products_sql);
$premium_products = $premium_products_result->fetch_all(MYSQLI_ASSOC);

?>

<!-- Hero Search Section -->
<div class="hero-search-section text-center py-5">
    <div class="container">
        <h1 class="display-5">Search for Lesson Notes</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="search.php" method="get" class="d-flex hero-search-form">
                    <input class="form-control form-control-lg" type="search" name="query" placeholder="Enter keywords, subject, or topic..." aria-label="Search">
                    <button class="btn btn-primary btn-lg" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <p class="lead mt-3">Your source for quality educational materials.</p>
            </div>
        </div>
    </div>
</div>

<!-- Freemium Packages Section -->
<?php if (!empty($freemium_products)): ?>
<div class="container my-5">
    <h2 class="text-center mb-4">Freemium Packages</h2>
    <div id="freemiumCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $chunks = array_chunk($freemium_products, 4);
            foreach($chunks as $index => $chunk):
            ?>
            <div class="carousel-item <?php if($index == 0) echo 'active'; ?>">
                <div class="row">
                    <?php foreach($chunk as $product): ?>
                        <div class="col-md-3 mb-4">
                            <?php include 'includes/product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#freemiumCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#freemiumCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Premium Packages Section -->
<?php if (!empty($premium_products)): ?>
<div class="container my-5">
    <h2 class="text-center mb-4">Premium Packages</h2>
    <div class="row">
        <?php foreach($premium_products as $product): ?>
            <div class="col-md-3 mb-4">
                <?php include 'includes/product_card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


<!-- How It Works Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">How It Works</h2>
    <div class="row text-center">
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-th-list"></i></div>
                <h5>1. Browse Packages</h5>
                <p>Visit our Subscriptions page to see the available lesson note packages.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                <h5>2. Subscribe</h5>
                <p>Add your desired package to the cart and proceed to checkout.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                <h5>3. Make Payment</h5>
                <p>Complete your payment using our secure Paystack gateway or via Bank Transfer.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-book-reader"></i></div>
                <h5>4. Get Instant Access</h5>
                <p>Once payment is confirmed, you get immediate access to your subscribed lesson notes.</p>
            </div>
        </div>
    </div>
</div>


<?php
// Include the footer
include 'includes/footer.php';
?>
