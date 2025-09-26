<?php
require_once 'templates/header.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$product_id = (int)$_GET['id'];

// Fetch the product details, including the author's username and category name
$query = "
    SELECT p.*, u.username, c.name as category_name
    FROM products p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.approved = 1
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger'>Product not found or not approved.</div>";
    require_once 'templates/footer.php';
    exit;
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <img src="<?php echo htmlspecialchars($product['image'] ?? 'assets/images/placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <hr>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="mb-4">$<?php echo number_format($product['price'], 2); ?></h2>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-primary btn-lg">Purchase Now</a>
                    <a href="#" class="btn btn-secondary">Add to Cart</a>
                </div>
                <hr>
                <ul class="list-unstyled">
                    <li><strong>Author:</strong> <?php echo htmlspecialchars($product['username']); ?></li>
                    <li><strong>Category:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span></li>
                    <li><strong>Released:</strong> <?php echo date('F j, Y', strtotime($product['created_at'])); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'templates/footer.php';
?>