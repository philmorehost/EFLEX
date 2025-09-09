<?php
// --- This part must be at the very top, before any HTML output ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';

// Check if user is logged in and has permission to be here.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !in_array($_SESSION['role'], ['admin', 'staff'])){
    header("location: ../index.php");
    exit;
}

// --- Handle form submission for updating the product ---
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])){
    $product_id = trim($_POST["id"]);
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];
    $files_to_delete = $_POST['delete_files'] ?? [];
    $html_content = $_POST['html_content'] ?? null;

    // Basic validation
    if(empty($name) || empty($description) || !isset($price) || empty($category_id)){
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Please fill in all required fields.'];
        header("location: edit_product.php?id=" . $product_id);
        exit();
    }

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
                if ($row['filepath'] && file_exists(__DIR__ . '/../' . $row['filepath'])) {
                    unlink(__DIR__ . '/../' . $row['filepath']);
                }
            }
            $stmt_get->close();

            $sql_delete_files = "DELETE FROM product_local_files WHERE id IN (".implode(',', array_fill(0, count($files_to_delete), '?')).")";
            $stmt_delete = $mysqli->prepare($sql_delete_files);
            $stmt_delete->bind_param(str_repeat('i', count($files_to_delete)), ...$files_to_delete);
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
                $new_upload_filename = uniqid('img_', true) . "." . $ext;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], __DIR__ . "/../uploads/" . $new_upload_filename)) {
                    $new_image_filename = $new_upload_filename;
                    if ($current_image !== 'default.jpg' && file_exists(__DIR__ . "/../uploads/" . $current_image)) {
                        unlink(__DIR__ . "/../uploads/" . $current_image);
                    }
                } else { throw new Exception("Failed to move uploaded image."); }
            } else { throw new Exception("Invalid image format."); }
        }

        // 3. Handle New File Uploads
        $uploaded_files = [];
        $protected_dir = __DIR__ . '/../uploads/protected_files/';
        if (isset($_FILES['product_files'])) {
            foreach ($_FILES['product_files']['name'] as $i => $name) {
                if ($_FILES['product_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $original_filename = basename($name);
                    $safe_filename = uniqid('prod_', true) . '.' . pathinfo($original_filename, PATHINFO_EXTENSION);
                    if (move_uploaded_file($_FILES['product_files']['tmp_name'][$i], $protected_dir . $safe_filename)) {
                        $uploaded_files[] = [
                            'filename' => $safe_filename, 'original_filename' => $original_filename, 'filepath' => 'uploads/protected_files/' . $safe_filename,
                            'mimetype' => $_FILES['product_files']['type'][$i], 'filesize' => $_FILES['product_files']['size'][$i]
                        ];
                    } else { throw new Exception("Error moving file: " . $original_filename); }
                } elseif ($_FILES['product_files']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                     throw new Exception("Error uploading file: " . $name);
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
        $sql_update = "UPDATE products SET name=?, description=?, html_content=?, price=?, duration_days=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
        $stmt_update = $mysqli->prepare($sql_update);
        $stmt_update->bind_param("sssdiisiii", $name, $description, $html_content, $price, $duration_days, $category_id, $new_image_filename, $is_featured, $is_top_seller, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        $mysqli->commit();
        $_SESSION['product_updated'] = "Class details updated successfully.";
        header("location: manage_products.php");
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Update failed: ' . $e->getMessage()];
        header("location: edit_product.php?id=" . $product_id);
        exit();
    }
}

// --- This part is for displaying the page (GET request) ---
include 'includes/header.php';
require_once '../includes/helpers.php';

$user_id_to_edit = trim($_GET["id"]);
$message = "";

// Display flash messages
if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    $message = '<div class="alert alert-'.htmlspecialchars($flash['type']).'">'.htmlspecialchars($flash['message']).'</div>';
    unset($_SESSION['flash_message']);
}

// Fetch current data for the form
$sql_fetch = "SELECT * FROM products WHERE id = ?";
$stmt_fetch = $mysqli->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $user_id_to_edit);
$stmt_fetch->execute();
$product = $stmt_fetch->get_result()->fetch_assoc();
$stmt_fetch->close();

if(!$product) {
    echo "Product not found."; exit;
}

// Fetch existing files for the product
$sql_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ?";
$stmt_files = $mysqli->prepare($sql_files);
$stmt_files->bind_param("i", $user_id_to_edit);
$stmt_files->execute();
$existing_files = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_files->close();

// Fetch categories for the dropdown
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Subscription Class</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Classes</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit"></i> Edit Class Details</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $user_id_to_edit; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $user_id_to_edit; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Class Name</label>
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
                               <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01">
                            </div>
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
                            <input type="number" name="duration_days" id="duration_days" class="form-control" value="<?php echo htmlspecialchars($product['duration_days']); ?>">
                            <div class="form-text">In days. 0 for lifetime.</div>
                        </div>
                    </div>
                     <hr>
                    <div class="mb-3">
                        <label class="form-label">Manage Class Files</label>
                        <?php if (!empty($existing_files)): ?>
                            <ul class="list-group mb-2">
                                <?php foreach($existing_files as $file): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($file['original_filename']); ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_files[]" value="<?php echo $file['id']; ?>" id="delete_file_<?php echo $file['id']; ?>">
                                            <label class="form-check-label text-danger" for="delete_file_<?php echo $file['id']; ?>">Delete</label>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="product_files" class="form-label">Upload New Files</label>
                        <input type="file" name="product_files[]" id="product_files" class="form-control" multiple>
                    </div>
                    <hr>
                    <div class="mb-3">
                         <label for="html_content" class="form-label">Web Content</label>
                         <textarea name="html_content" id="html_content" class="form-control" rows="10"><?php echo htmlspecialchars($product['html_content'] ?? ''); ?></textarea>
                     </div>
                </div>
                <div class="col-md-4">
                     <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" class="img-thumbnail mb-2" style="max-width: 150px;">
                        <label for="image" class="form-label">Change Image</label>
                        <input type="file" name="image" id="image" class="form-control">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($product['is_featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Premium</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($product['is_top_seller']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_top_seller">Freemium</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <a href="manage_products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="update_product" class="btn btn-primary">Update Class</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
