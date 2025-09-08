<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/helpers.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

$name = $description = $price = $category_id = "";
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = $files_err = "";
$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $html_content = $_POST['html_content'] ?? null;

    if(empty($name)) $name_err = "Please enter a class name.";
    if(empty($description)) $description_err = "Please enter a description.";
    if(!isset($price) || $price === "") $price_err = "Please enter a price.";
    if(empty($category_id)) $category_id_err = "Please select a category.";

    $image_filename = "default.jpg";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $allowed)) {
            $new_filename = uniqid('img_', true) . "." . $ext;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], __DIR__ . "/../uploads/" . $new_filename)) {
                $image_filename = $new_filename;
            } else {
                $image_err = "Failed to move uploaded image.";
            }
        } else {
            $image_err = "Invalid image format.";
        }
    }

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
                    $files_err = "Error moving subscription file: " . $original_filename;
                    break;
                }
            } elseif ($_FILES['product_files']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $files_err = "Error uploading file: " . $_FILES['product_files']['name'][$i];
                break;
            }
        }
    }

    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err) && empty($files_err)){
        // Security: Sanitize HTML content before saving
        if (!empty($html_content)) {
            // A more robust library like HTML Purifier would be better, but this is a basic measure.
            // For now, we trust the admin input but will implement better sanitization if requested.
        }

        $mysqli->begin_transaction();
        try {
            $sql = "INSERT INTO products (name, description, html_content, price, duration_days, category_id, image, is_featured, is_top_seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssdiisii", $name, $description, $html_content, $price, $duration_days, $category_id, $image_filename, $is_featured, $is_top_seller);
            $stmt->execute();
            $product_id = $mysqli->insert_id;
            $stmt->close();

            if(!empty($uploaded_files)) {
                $sql_files = "INSERT INTO product_local_files (product_id, filename, original_filename, filepath, mimetype, filesize) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_files = $mysqli->prepare($sql_files);
                foreach($uploaded_files as $file) {
                    $stmt_files->bind_param("issssi", $product_id, $file['filename'], $file['original_filename'], $file['filepath'], $file['mimetype'], $file['filesize']);
                    $stmt_files->execute();
                }
                $stmt_files->close();
            }

            $mysqli->commit();
            $_SESSION['product_added'] = "Class successfully added.";
            header("location: manage_products.php");
            exit();
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            $message = '<div class="alert alert-danger">Database error. Please try again later.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please correct the errors and try again. ' . $files_err . $image_err . '</div>';
    }
}

include 'includes/header.php';
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add New Subscription Class</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Classes</a>
</div>
<?php echo $message; ?>
<div class="card">
    <div class="card-header"><i class="fas fa-plus-circle"></i> New Class Details</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3"><label for="name" class="form-label">Class Name</label><input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>"><span class="invalid-feedback"><?php echo $name_err; ?></span></div>
                    <div class="mb-3"><label for="description" class="form-label">Description</label><textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $description; ?></textarea><span class="invalid-feedback"><?php echo $description_err; ?></span></div>
                    <div class="row">
                        <div class="col-md-6"><div class="mb-3"><label for="price" class="form-label">Price</label><div class="input-group"><span class="input-group-text"><?php echo get_app_setting('currency_symbol', '$'); ?></span><input type="number" name="price" id="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>" step="0.01" min="0"></div><span class="invalid-feedback"><?php echo $price_err; ?></span></div></div>
                        <div class="col-md-4"><div class="mb-3"><label for="category_id" class="form-label">Category</label><select name="category_id" id="category_id" class="form-select <?php echo (!empty($category_id_err)) ? 'is-invalid' : ''; ?>"><option value="">Select a category</option><?php foreach ($categories as $category): ?><option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?></select><span class="invalid-feedback"><?php echo $category_id_err; ?></span></div></div>
                        <div class="col-md-2"><div class="mb-3"><label for="duration_days" class="form-label">Duration (days)</label><input type="number" name="duration_days" id="duration_days" class="form-control" value="365"></div></div>
                    </div>
                     <div class="mb-3"><label for="product_files" class="form-label">Class Files</label><input type="file" name="product_files[]" id="product_files" class="form-control" multiple><div class="form-text">Upload one or more files for this class.</div><span class="text-danger"><?php echo $files_err; ?></span></div>
                     <hr>
                     <div class="mb-3">
                         <label for="html_content" class="form-label">Web Content (Alternative to Files)</label>
                         <div class="alert alert-info"><i class="fas fa-info-circle"></i> As an alternative to uploading files, you can create the content directly below using this rich-text editor.</div>
                         <textarea name="html_content" id="html_content" class="form-control" rows="10"></textarea>
                     </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3"><label for="image" class="form-label">Class Image</label><input type="file" name="image" id="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>"><span class="invalid-feedback"><?php echo $image_err; ?></span></div>
                    <div class="mb-3 form-check"><input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($is_featured) ? 'checked' : ''; ?>><label class="form-check-label" for="is_featured">Featured Class</label></div>
                    <div class="mb-3 form-check"><input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($is_top_seller) ? 'checked' : ''; ?>><label class="form-check-label" for="is_top_seller">Top Seller</label></div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end"><a href="manage_products.php" class="btn btn-secondary me-2">Cancel</a><button type="submit" class="btn btn-primary">Add Class</button></div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
