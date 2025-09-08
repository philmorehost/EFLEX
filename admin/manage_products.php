<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';
require_once '../includes/helpers.php';

$message = "";

// Handle Delete Product
if(isset($_GET['delete'])){
    $id_to_delete = $_GET['delete'];

    $mysqli->begin_transaction();
    try {
        // 1. Get and delete associated local files from server
        $sql_files = "SELECT filepath FROM product_local_files WHERE product_id = ?";
        $stmt_files = $mysqli->prepare($sql_files);
        $stmt_files->bind_param("i", $id_to_delete);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result();
        while($row = $result_files->fetch_assoc()) {
            $file_to_unlink = __DIR__ . '/../' . $row['filepath'];
            if (file_exists($file_to_unlink)) {
                unlink($file_to_unlink);
            }
        }
        $stmt_files->close();

        // 2. Delete records from product_local_files table
        $sql_delete_links = "DELETE FROM product_local_files WHERE product_id = ?";
        $stmt_delete_links = $mysqli->prepare($sql_delete_links);
        $stmt_delete_links->bind_param("i", $id_to_delete);
        $stmt_delete_links->execute();
        $stmt_delete_links->close();

        // 3. Get and delete the main product image from server
        $sql_img = "SELECT image FROM products WHERE id = ?";
        $stmt_img = $mysqli->prepare($sql_img);
        $stmt_img->bind_param("i", $id_to_delete);
        $stmt_img->execute();
        $stmt_img->bind_result($image_filename);
        $stmt_img->fetch();
        $stmt_img->close();
        if($image_filename && $image_filename != 'default.jpg' && file_exists("../uploads/" . $image_filename)){
            unlink("../uploads/" . $image_filename);
        }

        // 4. Delete the product itself
        $sql_delete_product = "DELETE FROM products WHERE id = ?";
        $stmt_delete_product = $mysqli->prepare($sql_delete_product);
        $stmt_delete_product->bind_param("i", $id_to_delete);
        $stmt_delete_product->execute();
        $stmt_delete_product->close();

        $mysqli->commit();
        header("location: manage_products.php?delete_success=1");
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $message = '<div class="alert alert-danger">Error deleting class: ' . $e->getMessage() . '</div>';
    }
}

if(isset($_GET['delete_success'])){
    $message = '<div class="alert alert-success">Class deleted successfully.</div>';
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
$sql = "SELECT p.id, p.name, p.price, p.duration_days, p.image, c.name as category_name,
               (SELECT COUNT(*) FROM product_local_files plf WHERE plf.product_id = p.id) as file_count
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
    $message .= '<div class="alert alert-danger">Error fetching classes.</div>';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Subscription Classes</h1>
    <a href="add_product.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Class</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-chalkboard"></i> Existing Classes
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Class Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Files</th>
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
                            <td><span class="badge bg-info"><?php echo $product['file_count']; ?></span></td>
                            <td class="text-end">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="manage_products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this class? This will also delete all associated files and cannot be undone.')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No classes found. <a href="add_product.php">Add one now</a>.</td></tr>
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
