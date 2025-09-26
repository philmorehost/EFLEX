<?php
require_once 'templates/header.php';

// Fetch all approved products from the database, joining with categories to get the category name.
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
    /* Add a subtle hover effect to the product cards for better UX. */
    .product-card {
        transition: transform .2s, box-shadow .2s;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>

<div class="p-5 mb-4 bg-light rounded-3 text-center">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold"><?php echo htmlspecialchars(get_setting('landing_headline') ?: 'Welcome to the Marketplace!'); ?></h1>
        <p class="col-md-8 fs-4 mx-auto"><?php echo htmlspecialchars(get_setting('landing_subheadline') ?: 'The ultimate destination for high-quality scripts, plugins, and templates. Find exactly what you need to bring your project to life.'); ?></p>
    </div>
</div>

<h2>Featured Products</h2>
<hr>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 shadow-sm product-card">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                        <img src="<?php echo htmlspecialchars($product['image'] ?? 'assets/images/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span></p>
                        </div>
                    </a>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">$<?php echo number_format($product['price'], 2); ?></span>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-dark">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No products have been approved yet. Please check back later.</div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer.php';
?>