<?php
// Include admin header
include 'includes/header.php';

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables
$name = $description = $price = $category_id = $current_image = "";
$duration_days = 365;
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = $files_err = "";
$message = "";
$product_id = 0;
$existing_files = [];

// Check if ID is provided for editing
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $product_id = trim($_GET["id"]);
} elseif(isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $product_id = trim($_POST["id"]);
} else {
    // If no ID, redirect to the list of products.
    $_SESSION['product_error'] = "Invalid product specified.";
    header("location: manage_products.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate form fields
    $name = trim($_POST["name"]);
    $description = $_POST["description"]; // Use raw HTML from CKEditor
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];
    $files_to_delete = $_POST['delete_files'] ?? [];

    // Basic validation
    if(empty($name)) $name_err = "Please enter a product name.";
    // For CKEditor, check if the description is empty after stripping HTML tags.
    if(empty(strip_tags($description))) $description_err = "Please enter a description.";
    if(!isset($price) || $price === "") $price_err = "Please enter a price.";
    if(empty($category_id)) $category_id_err = "Please select a category.";

    // --- Start Transaction ---
    $mysqli->begin_transaction();

    try {
        // 1. Handle Deleting Files
        if (!empty($files_to_delete)) {
            $sql_get_filenames = "SELECT filepath FROM product_local_files WHERE id IN (".implode(',', array_fill(0, count($files_to_delete), '?')).") AND product_id = ?";
            $stmt_get = $mysqli->prepare($sql_get_filenames);
            $types = str_repeat('i', count($files_to_delete)) . 'i';
            $params = array_merge($files_to_delete, [$product_id]);
            $stmt_get->bind_param($types, ...$params);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            while($row = $result_get->fetch_assoc()) {
                $file_to_unlink = __DIR__ . '/../' . $row['filepath'];
                if (file_exists($file_to_unlink)) {
                    unlink($file_to_unlink);
                }
            }
            $stmt_get->close();

            // Now delete from DB
            $sql_delete_files = "DELETE FROM product_local_files WHERE id IN (".implode(',', array_fill(0, count($files_to_delete), '?')).") AND product_id = ?";
            $stmt_delete = $mysqli->prepare($sql_delete_files);
            $stmt_delete->bind_param($types, ...$params);
            $stmt_delete->execute();
            $stmt_delete->close();
        }

        // 2. Handle Image Upload
        $new_image_filename = $current_image;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
            $filename = $_FILES["image"]["name"];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (array_key_exists($ext, $allowed)) {
                $new_filename = uniqid('img_', true) . "." . $ext;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], __DIR__ . "/../uploads/" . $new_filename)) {
                    $new_image_filename = $new_filename;
                    // Optionally delete old image if it's not the default
                    if ($current_image !== 'default.jpg' && file_exists(__DIR__ . "/../uploads/" . $current_image)) {
                        unlink(__DIR__ . "/../uploads/" . $current_image);
                    }
                } else {
                    throw new Exception("Failed to move uploaded image.");
                }
            } else {
                throw new Exception("Invalid image format.");
            }
        }

        // 3. Handle New File Uploads
        $uploaded_files = [];
        $protected_dir = __DIR__ . '/../uploads/protected_files/';
        if (isset($_FILES['product_files'])) {
            $file_count = count($_FILES['product_files']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['product_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $original_filename = basename($_FILES['product_files']['name'][$i]);
                    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
                    $safe_filename = uniqid('prod_', true) . '.' . $file_extension;
                    $destination = $protected_dir . $safe_filename;
                    if (move_uploaded_file($_FILES['product_files']['tmp_name'][$i], $destination)) {
                        $uploaded_files[] = [
                            'filename' => $safe_filename, 'original_filename' => $original_filename, 'filepath' => 'uploads/protected_files/' . $safe_filename, 'mimetype' => $_FILES['product_files']['type'][$i], 'filesize' => $_FILES['product_files']['size'][$i]
                        ];
                    } else {
                        throw new Exception("Error moving subscription file: " . $original_filename);
                    }
                } elseif ($_FILES['product_files']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                     throw new Exception("Error uploading file: " . $_FILES['product_files']['name'][$i]);
                }
            }
        }

        if (!empty($uploaded_files)) {
            $sql_files = "INSERT INTO product_local_files (product_id, filename, original_filename, filepath, mimetype, filesize) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_files = $mysqli->prepare($sql_files);
            foreach($uploaded_files as $file) {
                $stmt_files->bind_param("issssi", $product_id, $file['filename'], $file['original_filename'], $file['filepath'], $file['mimetype'], $file['filesize']);
                $stmt_files->execute();
            }
            $stmt_files->close();
        }

        // 4. Update Product Details
        $sql_update = "UPDATE products SET name=?, description=?, price=?, duration_days=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
        $stmt_update = $mysqli->prepare($sql_update);
        $stmt_update->bind_param("ssdiisiii", $name, $description, $price, $duration_days, $category_id, $new_image_filename, $is_featured, $is_top_seller, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        // --- Commit Transaction ---
        $mysqli->commit();

        $_SESSION['product_updated'] = "Package details updated successfully.";
        header("location: manage_products.php");
        exit();

    } catch (Exception $e) {
        // --- Rollback Transaction ---
        $mysqli->rollback();
        $message = '<div class="alert alert-danger">Update failed: ' . $e->getMessage() . '</div>';
    }

} else {
    // Fetch current data for the form if not a POST request
    $sql_fetch = "SELECT name, description, price, duration_days, category_id, image, is_featured, is_top_seller FROM products WHERE id = ?";
    if($stmt_fetch = $mysqli->prepare($sql_fetch)){
        $stmt_fetch->bind_param("i", $product_id);
        if($stmt_fetch->execute()){
            $result = $stmt_fetch->get_result();
            if($result->num_rows == 1){
                $product = $result->fetch_assoc();
                $name = $product['name'];
                $description = $product['description'];
                $price = $product['price'];
                $duration_days = $product['duration_days'];
                $category_id = $product['category_id'];
                $current_image = $product['image'];
                $is_featured = $product['is_featured'];
                $is_top_seller = $product['is_top_seller'];
            } else {
                $_SESSION['product_error'] = "Product not found.";
                header("location: manage_products.php");
                exit();
            }
        }
        $stmt_fetch->close();
    }

    // Fetch existing files for the product
    $sql_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ?";
    if($stmt_files = $mysqli->prepare($sql_files)) {
        $stmt_files->bind_param("i", $product_id);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result();
        $existing_files = $result_files->fetch_all(MYSQLI_ASSOC);
        $stmt_files->close();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Subscription Package</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Packages</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit"></i> Edit Package Details</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

            <div class="row">
                <div class="col-md-8">
                    <!-- Form fields for product details -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Package Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                             <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                               <span class="input-group-text"><?php echo get_app_setting('currency_symbol', '$'); ?></span>
                               <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="duration_days" class="form-label">Duration</label>
                            <input type="number" name="duration_days" id="duration_days" class="form-control" value="<?php echo htmlspecialchars($duration_days); ?>">
                            <div class="form-text">In days. 0 for lifetime.</div>
                        </div>
                    </div>
                     <hr>
                    <!-- File Management -->
                    <div class="mb-3">
                        <label class="form-label">Manage Subscription Files</label>
                        <?php if (!empty($existing_files)): ?>
                            <div class="mb-2">
                                <p>Current files:</p>
                                <ul class="list-group">
                                    <?php foreach($existing_files as $file): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo htmlspecialchars($file['original_filename']); ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="delete_files[]" value="<?php echo $file['id']; ?>" id="delete_file_<?php echo $file['id']; ?>">
                                                <label class="form-check-label text-danger" for="delete_file_<?php echo $file['id']; ?>">
                                                    Delete
                                                </label>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No files are currently associated with this package.</p>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="product_files" class="form-label">Upload New Files</label>
                        <input type="file" name="product_files[]" id="product_files" class="form-control" multiple>
                        <div class="form-text">Select one or more new files to add to this package.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Image and flags -->
                     <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img src="../uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" class="img-thumbnail mb-2" style="max-width: 150px;">
                        <label for="image" class="form-label">Change Image</label>
                        <input type="file" name="image" id="image" class="form-control">
                        <div class="form-text">Leave blank to keep the current image.</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($is_featured) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($is_top_seller) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_top_seller">Top Seller</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <a href="manage_products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    ClassicEditor
        .create( document.querySelector( '#description' ) )
        .catch( error => {
            console.error( error );
        } );
</script>

<?php
// Include admin footer
include 'includes/footer.php';
?>
