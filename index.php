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
    // Unset the session variable so it doesn't show again
    unset($_SESSION['admin_created']);
}
?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1>Welcome to Eflex</h1>
        <p class="lead">Your one-stop shop for everything you need. We offer the best products at the best prices.</p>
        <a class="btn btn-primary btn-lg" href="products.php" role="button">Shop Now</a>
    </div>
</div>

<!-- Featured Products Section -->
<h2>Featured Products</h2>
<div class="row">
    <!-- Product Card 1 -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <img src="https://via.placeholder.com/300" class="card-img-top" alt="Product Image">
            <div class="card-body">
                <h5 class="card-title">Product Name 1</h5>
                <p class="card-text">Short description of the product. This is a placeholder.</p>
                <p class="card-text"><strong>$19.99</strong></p>
                <a href="#" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>
    <!-- Product Card 2 -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <img src="https://via.placeholder.com/300" class="card-img-top" alt="Product Image">
            <div class="card-body">
                <h5 class="card-title">Product Name 2</h5>
                <p class="card-text">Short description of the product. This is a placeholder.</p>
                <p class="card-text"><strong>$29.99</strong></p>
                <a href="#" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>
    <!-- Product Card 3 -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <img src="https://via.placeholder.com/300" class="card-img-top" alt="Product Image">
            <div class="card-body">
                <h5 class="card-title">Product Name 3</h5>
                <p class="card-text">Short description of the product. This is a placeholder.</p>
                <p class="card-text"><strong>$39.99</strong></p>
                <a href="#" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
