<?php
$body_class = 'home-page'; // Add a class to the body for page-specific styling
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
<div class="hero-search-section text-center py-3">
    <div class="container">
        <h1 class="display-6">Search for Lesson Notes</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="search.php" method="get" class="d-flex hero-search-form">
                    <input class="form-control form-control-lg" type="search" name="query" placeholder="Enter keywords, subject, or topic..." aria-label="Search">
                    <button class="btn btn-primary btn-lg" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Packages Section -->
<div class="container my-3">
    <div class="row">
        <!-- Freemium Column -->
        <?php if (!empty($freemium_products)): ?>
        <div class="col-lg-6">
            <h2 class="text-center mb-4">Freemium Packages</h2>
            <div id="freemiumCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    // Adjust chunk size for a 2-up display in the carousel
                    $chunks = array_chunk($freemium_products, 2);
                    foreach($chunks as $index => $chunk):
                    ?>
                    <div class="carousel-item <?php if($index == 0) echo 'active'; ?>">
                        <div class="row">
                            <?php foreach($chunk as $product): ?>
                                <div class="col-6 col-md-6 mb-4">
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

        <!-- Premium Column -->
        <?php if (!empty($premium_products)): ?>
        <div class="col-lg-6">
            <h2 class="text-center mb-4">Premium Packages</h2>
            <div class="row">
                <?php foreach($premium_products as $product): ?>
                    <div class="col-6 col-md-6 mb-4">
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
                <div class="step-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                        <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                        <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </div>
                <h5>1. Browse Packages</h5>
                <p>Visit our Subscriptions page to see the available lesson note packages.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16">
                        <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/>
                        <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                </div>
                <h5>2. Subscribe</h5>
                <p>Add your desired package to the cart and proceed to checkout.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                        <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                    </svg>
                </div>
                <h5>3. Make Payment</h5>
                <p>Complete your payment using our secure Paystack gateway or via Bank Transfer.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-book-half" viewBox="0 0 16 16">
                        <path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388 1.175.885.652 1.453 1.474 1.453 2.344 0 .52-.144.973-.374 1.353-.23.38-.554.635-.905.795-1.12.502-2.435.347-3.71-.167C11.176 7.23 10.383 7 9.5 7c-.63 0-1.2.113-1.664.333C7.4 7.558 6.818 7.75 6 8.049V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5V3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v.542c-.03-.02-.06-.038-.09-.056C14.07 3.598 12.87 3.25 11.5 3.25c-.866 0-1.794.196-2.688.687-1.138.624-2.228 1.21-3.21 1.21-.346 0-.69-.035-.998-.108V3.5A1.5 1.5 0 0 1 6 2h2.5a.5.5 0 0 1 .5.5zM8 12.5a.5.5 0 0 0-.5-.5H6v1h1.5a.5.5 0 0 0 .5-.5zM8.5 4.032C9.59 3.443 10.73 3.25 11.5 3.25c.78 0 1.5.15 2.148.455C14.37 4.042 15 4.67 15 5.25c0 .302-.132.55-.31.733-.178.183-.42.3-1.02.443-1.01.24-2.03.11-2.825-.115C9.92 6.13 9.24 6 8.5 6c-.57 0-1.038.115-1.42.29-.38.175-.71.38-1.02.605V4.75c.3-.07.65-.108.998-.108.982 0 2.072.586 3.21 1.21z"/>
                    </svg>
                </div>
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
