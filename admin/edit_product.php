<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables
$name = $description = $price = $category_id = $current_image = $google_drive_folder_id = "";
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = "";
$message = "";
$product_id = 0;

// Check if ID is provided for editing
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $product_id = trim($_GET["id"]);
} elseif(isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $product_id = trim($_POST["id"]);
} else {
    header("location: manage_products.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate form fields
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $google_drive_folder_id = trim($_POST['google_drive_folder_id']);
    $duration_days = (int)$_POST['duration_days'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];

    // (Validation logic here...)

    $new_image_filename = $current_image;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        // ... (image upload logic) ...
    }

    // Check input errors before updating database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err)){
        $sql = "UPDATE products SET name=?, description=?, price=?, duration_days=?, category_id=?, image=?, google_drive_folder_id=?, is_featured=?, is_top_seller=? WHERE id=?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdiisssii", $name, $description, $price, $duration_days, $category_id, $new_image_filename, $google_drive_folder_id, $is_featured, $is_top_seller, $product_id);
            if($stmt->execute()){
                $_SESSION['product_updated'] = "Package details updated successfully.";
                header("location: manage_products.php");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Update failed. Please try again.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Please correct the errors and try again.</div>';
    }
} else {
    // Fetch current data for the form
    $sql_fetch = "SELECT name, description, price, duration_days, category_id, image, google_drive_folder_id, is_featured, is_top_seller FROM products WHERE id = ?";
    if($stmt_fetch = $mysqli->prepare($sql_fetch)){
        $stmt_fetch->bind_param("i", $product_id);
        if($stmt_fetch->execute()){
            $stmt_fetch->store_result();
            if($stmt_fetch->num_rows == 1){
                $stmt_fetch->bind_result($name, $description, $price, $duration_days, $category_id, $current_image, $google_drive_folder_id, $is_featured, $is_top_seller);
                $stmt_fetch->fetch();
            } else {
                header("location: manage_products.php");
                exit();
            }
        }
        $stmt_fetch->close();
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
                        <label for="name" class="form-label">Product Name</label>
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
                            <input type="number" name="duration_days" id="duration_days" class="form-control" value="<?php echo htmlspecialchars($duration_days ?? 365); ?>">
                            <div class="form-text">In days.</div>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="google_drive_folder_id" class="form-label">Google Drive Folder ID</label>
                        <input type="text" name="google_drive_folder_id" id="google_drive_folder_id" class="form-control" value="<?php echo htmlspecialchars($google_drive_folder_id); ?>">
                        <div class="form-text">If this product is a subscription for lesson notes, paste the Google Drive Folder ID here.</div>
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

<?php
// Include admin footer
include 'includes/footer.php';
?>
