<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();
protect_admin_page();

// Handle product status changes (approve, unapprove) and deletions.
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $product_id = (int)$_GET['id'];

    if ($action === 'approve') {
        $stmt = $mysqli->prepare("UPDATE products SET approved = 1 WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
    } elseif ($action === 'unapprove') {
        $stmt = $mysqli->prepare("UPDATE products SET approved = 0 WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        // In a real-world scenario, you would also delete the associated files from the server here.
        // e.g., unlink($product['image']); unlink($product['file']);
        $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
    }
    redirect('manage_products.php');
}

// Fetch all products with their author and category information for display.
$query = "
    SELECT p.id, p.name, p.price, p.approved, p.created_at, u.username, c.name as category_name
    FROM products p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
";
$products = $mysqli->query($query);

require_once 'partials/admin_header.php';
?>

<h1 class="h3 mb-2 text-gray-800">Manage Products</h1>
<p class="mb-4">Here you can approve, reject, and manage all user-submitted products.</p>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Submitted Products</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Submitted On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['username']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <?php if ($product['approved']): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$product['approved']): ?>
                                    <a href="manage_products.php?action=approve&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <?php else: ?>
                                    <a href="manage_products.php?action=unapprove&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Unapprove</a>
                                <?php endif; ?>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="manage_products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This cannot be undone.')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'partials/admin_footer.php';
?>