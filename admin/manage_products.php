<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';
require_once '../includes/helpers.php';

$message = "";

// Handle Delete Product
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    // First, get the image filename to delete it from the server
    $sql_img = "SELECT image FROM products WHERE id = ?";
    if($stmt_img = $mysqli->prepare($sql_img)){
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $stmt_img->bind_result($image_filename);
        $stmt_img->fetch();
        $stmt_img->close();

        // Delete the image file if it's not the default one
        if($image_filename && $image_filename != 'default.jpg' && file_exists("../uploads/" . $image_filename)){
            unlink("../uploads/" . $image_filename);
        }
    }

    // Now, delete the product record from the database
    $sql = "DELETE FROM products WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
             // Redirect to avoid re-deleting on refresh
             header("location: manage_products.php?delete_success=1");
             exit();
        } else {
            $message = '<div class="alert alert-danger">Error deleting product. Please try again.</div>';
        }
        $stmt->close();
    }
}

if(isset($_GET['delete_success'])){
    $message = '<div class="alert alert-success">Product deleted successfully.</div>';
}
if(isset($_SESSION['product_added'])){
    $message = '<div class="alert alert-success">'.$_SESSION['product_added'].'</div>';
    unset($_SESSION['product_added']);
}
if(isset($_SESSION['product_updated'])){
    $message = '<div class="alert alert-success">'.$_SESSION['product_updated'].'</div>';
    unset($_SESSION['product_updated']);
}


// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total number of products
$total_records_result = $mysqli->query("SELECT COUNT(*) FROM products");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);


// Fetch products for the current page
$sql = "SELECT p.id, p.name, p.price, p.duration_days, p.image, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.name ASC
        LIMIT ? OFFSET ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle error
    $products = [];
    $message .= '<div class="alert alert-danger">Error fetching products.</div>';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Subscription Packages</h1>
    <a href="add_product.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Package</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-box"></i> Existing Packages
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Package Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo format_price($product['price']); ?></td>
                            <td><?php echo htmlspecialchars($product['duration_days']); ?> days</td>
                            <td class="text-end">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="manage_products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No packages found. <a href="add_product.php">Add one now</a>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <!-- Pagination -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center mb-0">
            <?php if($page > 1): ?>
                <li class="page-item"><a class="page-link" href="manage_products.php?page=<?php echo $page-1; ?>">Previous</a></li>
            <?php endif; ?>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_products.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <?php if($page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="manage_products.php?page=<?php echo $page+1; ?>">Next</a></li>
            <?php endif; ?>
          </ul>
        </nav>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
