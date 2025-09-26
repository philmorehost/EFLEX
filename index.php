<?php
require_once __DIR__ . '/templates/header.php';

// Check if the config file exists. If not, redirect to the installer.
if (!file_exists('includes/config.php')) {
    header('Location: install/index.php');
    exit;
}

// Fetch approved products from the database
$query = "
    SELECT p.id, p.name, p.price, p.image, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.approved = 1
    ORDER BY p.created_at DESC
";
$products = $mysqli->query($query);

?>

<style>
    .product-card {
        transition: box-shadow .3s;
    }
    .product-card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>

<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Welcome to CodesterClone!</h1>
        <p class="col-md-8 fs-4">The ultimate marketplace for scripts, plugins, and templates. Find exactly what you need to bring your project to life.</p>
    </div>
</div>

<h2>Featured Products</h2>
<hr>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php if ($products->num_rows > 0): ?>
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 shadow-sm product-card">
                    <img src="<?php echo htmlspecialchars($product['image'] ?? 'assets/images/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text"><span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">$<?php echo number_format($product['price'], 2); ?></span>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-dark">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col">
            <p>No products have been approved yet. Please check back later.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer.php';
?>