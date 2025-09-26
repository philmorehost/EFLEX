<?php
require_once __DIR__ . '/../includes/init.php';

protect_admin_page();

if (!isset($_GET['id'])) {
    redirect('manage_products.php');
}

$product_id = (int)$_GET['id'];

// Fetch product data
$stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    redirect('manage_products.php');
}

// Fetch categories for the dropdown
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");

$errors = [];
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];

    if (empty($name) || empty($description) || empty($category_id)) {
        $errors[] = "Name, description, and category are required.";
    }

    if (empty($errors)) {
        // For file uploads, you'd add more logic here to handle the uploaded files
        // For simplicity, we are just updating the text fields
        $stmt = $mysqli->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?");
        $stmt->bind_param('ssdis', $name, $description, $price, $category_id, $product_id);

        if ($stmt->execute()) {
            $success = "Product updated successfully.";
            // Refresh product data
            $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $errors[] = "Failed to update product: " . $stmt->error;
        }
    }
}

require_once 'partials/admin_header.php';
?>

<h1 class="h3 mb-2 text-gray-800">Edit Product</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Editing: <?php echo htmlspecialchars($product['name']); ?></h6>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?><p><?php echo $error; ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <p class="text-muted">File upload logic would be added here to replace the product image and download file.</p>

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="manage_products.php" class="btn btn-secondary">Back to Products</a>
        </form>
    </div>
</div>

<?php
require_once 'partials/admin_footer.php';
?>