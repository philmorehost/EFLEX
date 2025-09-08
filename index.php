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

// Fetch classes for the carousels
// Freemium classes are those with a price of 0
$freemium_products_sql = "SELECT * FROM products WHERE price <= 0 ORDER BY created_at DESC LIMIT 8";
$freemium_products_result = $mysqli->query($freemium_products_sql);
$freemium_products = $freemium_products_result->fetch_all(MYSQLI_ASSOC);

// Premium classes are featured and have a price > 0
$premium_products_sql = "SELECT * FROM products WHERE is_featured = 1 AND price > 0 ORDER BY created_at DESC LIMIT 8";
$premium_products_result = $mysqli->query($premium_products_sql);
$premium_products = $premium_products_result->fetch_all(MYSQLI_ASSOC);

?>

<style>
    .hero-section-search {
        background: #f8f9fa;
        padding: 6rem 0;
    }
    .hero-section-search h1 {
        font-size: 2.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .search-form-wrapper {
        max-width: 600px;
        margin: auto;
    }
    .search-form-wrapper .form-control {
        height: 50px;
        padding-left: 20px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .search-form-wrapper .btn {
        height: 50px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .hero-section-search .lead {
        margin-top: 1rem;
        font-size: 1.1rem;
        color: #6c757d;
    }
</style>
<div class="hero-section-search text-center">
    <div class="container">
        <h1>Search for Lesson Notes</h1>
        <div class="search-form-wrapper">
            <form action="search.php" method="get" class="d-flex">
                <input class="form-control" type="search" name="query" placeholder="Enter a subject, topic, or keyword..." aria-label="Search">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <p class="lead">Your source for quality educational materials.</p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Freemium Classes Section -->
        <?php if (!empty($freemium_products)): ?>
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h2 class="text-center mb-4">Freemium Classes</h2>
            <div class="row">
                <?php foreach($freemium_products as $class): ?>
                    <div class="col-md-6 mb-4">
                        <?php include 'includes/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Premium Classes Section -->
        <?php if (!empty($premium_products)): ?>
        <div class="col-lg-6">
            <h2 class="text-center mb-4">Premium Classes</h2>
            <div class="row">
                <?php foreach($premium_products as $class): ?>
                    <div class="col-md-6 mb-4">
                        <?php include 'includes/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<!-- How It Works Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">How It Works</h2>
    <div class="row text-center">
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-th-list"></i></div>
                <h5>1. Browse Classes</h5>
                <p>Visit our Subscriptions page to see the available lesson note classes.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                <h5>2. Subscribe</h5>
                <p>Add your desired class to the cart and proceed to checkout.</p>
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
