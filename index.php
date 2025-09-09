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
        padding: 3rem 0; /* Reduced padding */
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
    /* .lead class is no longer used here */
    .how-it-works-svg {
        width: 40px;
        height: 40px;
    }
</style>
<div class="hero-section-search text-center">
    <div class="container">
        <h1>Search for Lesson Notes</h1>
        <div class="search-form-wrapper mb-3"> <!-- Added margin-bottom to search wrapper -->
            <form action="search.php" method="get" class="d-flex">
                <input class="form-control" type="search" name="query" placeholder="Enter a subject, topic, or keyword..." aria-label="Search">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <!-- Removed the lead text -->
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <?php
        $has_freemium = !empty($freemium_products);
        $has_premium = !empty($premium_products);
        $column_class = ($has_freemium && $has_premium) ? 'col-lg-6' : 'col-12';
        ?>

        <!-- Freemium Classes Section -->
        <?php if ($has_freemium): ?>
        <div class="<?php echo $column_class; ?> mb-5 mb-lg-0">
            <h2 class="text-center mb-4">Freemium Classes</h2>
            <div class="row">
                <?php foreach($freemium_products as $class): ?>
                    <div class="col-6 mb-4">
                        <?php include 'includes/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Premium Classes Section -->
        <?php if ($has_premium): ?>
        <div class="<?php echo $column_class; ?>">
            <h2 class="text-center mb-4">Premium Classes</h2>
            <div class="row">
                <?php foreach($premium_products as $class): ?>
                    <div class="col-6 mb-4">
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
                    <svg class="how-it-works-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </div>
                <h5>1. Browse Classes</h5>
                <p>Visit our Subscriptions page to see the available lesson note classes.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg class="how-it-works-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9z"/>
                      <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                    </svg>
                </div>
                <h5>2. Subscribe</h5>
                <p>Add your desired class to the cart and proceed to checkout.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg class="how-it-works-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
                      <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                    </svg>
                </div>
                <h5>3. Make Payment</h5>
                <p>Complete your payment using our secure Paystack gateway or via Bank Transfer.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <div class="step-icon">
                    <svg class="how-it-works-svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                      <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
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
