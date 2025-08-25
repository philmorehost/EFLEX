<?php
// Include the header and database connection
include 'includes/header.php';
require_once 'includes/db_connect.php';

// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 8; // Show 8 products per page
$offset = ($page - 1) * $records_per_page;

// Get total number of products
$total_records_result = $mysqli->query("SELECT COUNT(*) FROM products");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch products for the current page
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT ? OFFSET ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $products = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Products</h2>
</div>

<div class="row">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><a href="product_detail.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($product['name']); ?></a></h5>
                        <p class="card-text text-muted">$<?php echo htmlspecialchars($product['price']); ?></p>
                        <div class="mt-auto">
                             <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">Add to Cart</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col">
            <p>No products have been added yet.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <?php if($page > 1): ?>
    <li class="page-item"><a class="page-link" href="products.php?page=<?php echo $page-1; ?>">Previous</a></li>
    <?php endif; ?>

    <?php for($i = 1; $i <= $total_pages; $i++): ?>
    <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="products.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
    <?php endfor; ?>

    <?php if($page < $total_pages): ?>
    <li class="page-item"><a class="page-link" href="products.php?page=<?php echo $page+1; ?>">Next</a></li>
    <?php endif; ?>
  </ul>
</nav>

<?php
// Include the footer
include 'includes/footer.php';
?>
