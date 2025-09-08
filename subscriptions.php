<?php
// Include the header
include 'includes/header.php';

// Fetch all subscription classes
$sql = "SELECT * FROM products ORDER BY price ASC";
$result = $mysqli->query($sql);
$packages = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2>Subscription Classes</h2>
        <p class="lead">Choose a package to get access to our library of lesson notes.</p>
    </div>

    <div class="row">
        <?php if (count($packages) > 0): ?>
            <?php foreach ($packages as $package): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <h6 class="card-price text-center mb-4"><?php echo format_price($package['price']); ?>
                                <small class="text-muted">/ <?php echo htmlspecialchars($package['duration_days']); ?> days</small>
                            </h6>
                            <div class="description mb-4">
                                <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                            </div>
                            <div class="mt-auto">
                                <a href="cart.php?action=add&id=<?php echo $package['id']; ?>" class="btn btn-primary w-100">Subscribe Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No subscription packages are available at this time. Please check back later.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card-price {
    font-size: 2.5rem;
    font-weight: 700;
}
.description {
    min-height: 100px;
}
</style>

<?php
// Include the footer
include 'includes/footer.php';
?>
