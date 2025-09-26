<?php
require_once 'templates/header.php';
protect_page(); // Ensure user is logged in

$errors = [];
$success = null;

// Fetch categories for the dropdown
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $user_id = get_current_user()['id'];

    // --- File Upload Logic ---
    $upload_dir = 'uploads/';
    $image_path = null;
    $file_path = null;

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
        $image_target = $upload_dir . 'images/' . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_target)) {
            $image_path = $image_target;
        } else {
            $errors[] = "Failed to upload image.";
        }
    } else {
        $errors[] = "A product image is required.";
    }

    // Handle File Upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = uniqid() . '-' . basename($_FILES['file']['name']);
        $file_target = $upload_dir . 'files/' . $file_name;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_target)) {
            $file_path = $file_target;
        } else {
            $errors[] = "Failed to upload the product file.";
        }
    } else {
        $errors[] = "The main product file is required.";
    }
    // --- End File Upload Logic ---

    if (empty($name) || empty($description) || empty($category_id)) {
        $errors[] = "Name, description, and category are required.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO products (user_id, category_id, name, description, price, image, file) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisdsss', $user_id, $category_id, $name, $description, $price, $image_path, $file_path);

        if ($stmt->execute()) {
            $success = "Your product has been submitted for review!";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}

?>

<h1 class="mb-4">Submit a New Product</h1>

<div class="card shadow-sm">
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
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select a category...</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                     <div class="mb-3">
                        <label for="image" class="form-label">Preview Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <small class="form-text text-muted">A screenshot or thumbnail for your product (e.g., JPG, PNG).</small>
                    </div>
                </div>
                 <div class="col-md-6">
                     <div class="mb-3">
                        <label for="file" class="form-label">Main Product File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <small class="form-text text-muted">The actual file users will download (e.g., a .zip archive).</small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit for Review</button>
        </form>
    </div>
</div>


<?php
require_once 'templates/footer.php';
?>