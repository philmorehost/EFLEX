<?php
// Initialize session and connect to DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/helpers.php';

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// --- Get Product ID ---
$product_id = $_GET['id'] ?? 0;
if (!$product_id) {
    header("location: manage_products.php");
    exit();
}

$message = "";
$name_err = $description_err = $price_err = $category_id_err = $image_err = $files_err = "";

// --- Handle File Deletion (GET request for simplicity) ---
if(isset($_GET['delete_file']) && !empty($_GET['delete_file'])) {
    $file_id_to_delete = $_GET['delete_file'];

    // Get file path to delete from server
    $sql_file = "SELECT filepath FROM product_local_files WHERE id = ? AND product_id = ?";
    $stmt_file = $mysqli->prepare($sql_file);
    $stmt_file->bind_param("ii", $file_id_to_delete, $product_id);
    $stmt_file->execute();
    $stmt_file->bind_result($filepath);
    $stmt_file->fetch();
    $stmt_file->close();

    if ($filepath && file_exists(__DIR__ . '/../' . $filepath)) {
        unlink(__DIR__ . '/../' . $filepath);
    }

    // Delete the record from the database
    $sql_delete = "DELETE FROM product_local_files WHERE id = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("i", $file_id_to_delete);
    $stmt_delete->execute();
    $stmt_delete->close();

    $_SESSION['product_updated'] = "File deleted successfully.";
    header("location: edit_product.php?id=" . $product_id);
    exit();
}


// --- Processing form data when form is submitted ---
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])){
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];
    $new_image_filename = $current_image;

    // Handle new image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $allowed)) {
            $new_filename = uniqid('img_', true) . "." . $ext;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], __DIR__ . "/../uploads/" . $new_filename)) {
                if ($current_image && $current_image != 'default.jpg' && file_exists(__DIR__ . "/../uploads/" . $current_image)) {
                    unlink(__DIR__ . "/../uploads/" . $current_image);
                }
                $new_image_filename = $new_filename;
            }
        }
    }

    // Handle new subscription file uploads
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
                }
            }
        }
    }

    // Update product details in the database
    $sql = "UPDATE products SET name=?, description=?, price=?, duration_days=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssdiisiii", $name, $description, $price, $duration_days, $category_id, $new_image_filename, $is_featured, $is_top_seller, $product_id);
    $stmt->execute();
    $stmt->close();

    // Add new file records to the database
    if(!empty($uploaded_files)) {
        $sql_files = "INSERT INTO product_local_files (product_id, filename, original_filename, filepath, mimetype, filesize) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_files = $mysqli->prepare($sql_files);
        foreach($uploaded_files as $file) {
            $stmt_files->bind_param("issssi", $product_id, $file['filename'], $file['original_filename'], $file['filepath'], $file['mimetype'], $file['filesize']);
            $stmt_files->execute();
        }
        $stmt_files->close();
    }

    $_SESSION['product_updated'] = "Package details updated successfully.";
    header("location: manage_products.php");
    exit();
}

// --- Fetch current data for the form (for GET request) ---
$sql_fetch = "SELECT * FROM products WHERE id = ?";
$stmt_fetch = $mysqli->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $product_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$product = $result->fetch_assoc();
$stmt_fetch->close();

if(!$product) {
    header("location: manage_products.php");
    exit();
}

// Fetch associated local files
$sql_files = "SELECT * FROM product_local_files WHERE product_id = ?";
$stmt_files = $mysqli->prepare($sql_files);
$stmt_files->bind_param("i", $product_id);
$stmt_files->execute();
$result_files = $stmt_files->get_result();
$existing_files = $result_files->fetch_all(MYSQLI_ASSOC);
$stmt_files->close();

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

if(isset($_SESSION['product_updated'])){
    $message = '<div class="alert alert-success">'.$_SESSION['product_updated'].'</div>';
    unset($_SESSION['product_updated']);
}

// --- Start HTML Output ---
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Subscription Package</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Packages</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit"></i> Edit Package Details</div>
    <div class="card-body">
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                             <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                               <span class="input-group-text"><?php echo get_app_setting('currency_symbol', '$'); ?></span>
                               <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" min="0">
                            </div>
                             <div class="form-text">Enter 0 for a Freemium package.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="duration_days" class="form-label">Duration</label>
                            <input type="number" name="duration_days" id="duration_days" class="form-control" value="<?php echo htmlspecialchars($product['duration_days'] ?? 365); ?>">
                            <div class="form-text">In days.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Existing Files</label>
                        <?php if(count($existing_files) > 0): ?>
                            <ul class="list-group">
                                <?php foreach($existing_files as $file): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($file['original_filename']); ?> (<?php echo round($file['filesize'] / 1024); ?> KB)
                                        <a href="edit_product.php?id=<?php echo $product_id; ?>&delete_file=<?php echo $file['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this file?');"><i class="fas fa-trash"></i></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No files have been uploaded for this package yet.</p>
                        <?php endif; ?>
                    </div>

                     <div class="mb-3">
                        <label for="product_files" class="form-label">Upload New Files</label>
                        <input type="file" name="product_files[]" id="product_files" class="form-control" multiple>
                        <div class="form-text">You can upload additional files here to add to the package.</div>
                    </div>

                </div>
                <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" class="img-thumbnail mb-2" style="max-width: 150px;">
                        <label for="image" class="form-label">Change Image</label>
                        <input type="file" name="image" id="image" class="form-control">
                        <div class="form-text">Leave blank to keep the current image.</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($product['is_featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($product['is_top_seller']) ? 'checked' : ''; ?>>
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

<?php
// Include admin footer
include 'includes/footer.php';
?>
