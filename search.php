<?php
// Include header and database connection
include 'includes/header.php';
require_once 'includes/db_connect.php';

// Get search query
$query = isset($_GET['query']) ? $_GET['query'] : '';
$safe_query = htmlspecialchars($query);
$search_results = [];

if(!empty($query)){
    // Prepare the search term for a LIKE query
    $search_term = "%" . $query . "%";

    // SQL to search in product name and description
    $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";

    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $search_results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<div class="container mt-5">
    <h2>Search Results for: "<?php echo $safe_query; ?>"</h2>
    <hr>

    <div class="row">
        <?php if (count($search_results) > 0): ?>
            <?php foreach ($search_results as $product): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><a href="product_detail.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($product['name']); ?></a></h5>
                            <p class="card-text text-muted">$<?php echo htmlspecialchars($product['price']); ?></p>
                            <div class="mt-auto">
                                 <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary ajax-add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif(!empty($query)): ?>
            <div class="col">
                <p>No products found matching your search criteria.</p>
            </div>
        <?php else: ?>
             <div class="col">
                <p>Please enter a search term to find products.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
