<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables and initialize
$name = $description = $price = $category_id = $google_drive_folder_id = "";
$is_featured = $is_top_seller = 0;
$name_err = $description_err = $price_err = $category_id_err = $image_err = "";
$message = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate form fields
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = $_POST["category_id"];
    $google_drive_folder_id = trim($_POST['google_drive_folder_id']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;

    // (Existing validation logic for name, desc, price, category)
    if(empty($name)) $name_err = "Please enter a product name.";
    if(empty($description)) $description_err = "Please enter a description.";
    if(empty($price)) $price_err = "Please enter a price.";
    if(empty($category_id)) $category_id_err = "Please select a category.";

    // Handle image upload
    $image_filename = "default.jpg";
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        // ... (existing image upload logic) ...
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(array_key_exists($ext, $allowed)){
            $new_filename = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_filename)){
                $image_filename = $new_filename;
            }
        }
    }

    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err)){

        $sql = "INSERT INTO products (name, description, price, category_id, image, google_drive_folder_id, is_featured, is_top_seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdisssi", $name, $description, $price, $category_id, $image_filename, $google_drive_folder_id, $is_featured, $is_top_seller);

            if($stmt->execute()){
                $_SESSION['product_added'] = "Product successfully added.";
                header("location: manage_products.php");
                exit();
            } else{
                $message = '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
            }
            $stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">Please correct the errors and try again.</div>';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add New Product</h1>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> New Product Details
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="invalid-feedback"><?php echo $name_err; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $description; ?></textarea>
                        <span class="invalid-feedback"><?php echo $description_err; ?></span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" id="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>" step="0.01">
                                    <span class="invalid-feedback"><?php echo $price_err; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select <?php echo (!empty($category_id_err)) ? 'is-invalid' : ''; ?>">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="invalid-feedback"><?php echo $category_id_err; ?></span>
                            </div>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="google_drive_folder_id" class="form-label">Google Drive Folder ID</label>
                        <input type="text" name="google_drive_folder_id" id="google_drive_folder_id" class="form-control" value="<?php echo $google_drive_folder_id; ?>">
                        <div class="form-text">If this product is a subscription for lesson notes, paste the Google Drive Folder ID here.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" name="image" id="image" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $image_err; ?></span>
                        <div class="form-text">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF.</div>
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
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
