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
$new_products_sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$new_products_result = $mysqli->query($new_products_sql);
$new_products = $new_products_result->fetch_all(MYSQLI_ASSOC);

$featured_products_sql = "SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 8";
$featured_products_result = $mysqli->query($featured_products_sql);
$featured_products = $featured_products_result->fetch_all(MYSQLI_ASSOC);

?>

<!-- Hero Section -->
<div class="hero-section bg-dark text-white text-center">
    <div class="container">
        <h1 class="display-4">Search for Lesson Notes</h1>
        <div class="hero-search-form">
            <form action="search.php" method="get" class="d-flex">
                <input class="form-control form-control-lg" type="search" name="query" placeholder="Enter keywords, subject, or class..." aria-label="Search">
                <button class="btn btn-primary btn-lg" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<!-- New Arrivals Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">New Arrivals</h2>
    <div id="newArrivalsCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $chunks = array_chunk($new_products, 4);
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
        <button class="carousel-control-prev" type="button" data-bs-target="#newArrivalsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#newArrivalsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<!-- Featured Products Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row">
        <?php foreach($featured_products as $product): ?>
            <div class="col-md-3 mb-4">
                <?php include 'includes/product_card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

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
