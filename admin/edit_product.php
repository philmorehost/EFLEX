<?php
// Include the new admin header
include 'includes/admin_header.php';

// Fetch categories
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables
$name = $description = $price = $category_id = $current_image = "";
$is_featured = $is_top_seller = 0;
$message = "";
$product_id = 0;

// Check if ID is provided for editing
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $product_id = trim($_GET["id"]);
    $sql = "SELECT name, description, price, category_id, image, is_featured, is_top_seller FROM products WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $product_id);
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($name, $description, $price, $category_id, $current_image, $is_featured, $is_top_seller);
                $stmt->fetch();
            } else{
                // Redirect if product not found
                echo "<script>window.location.href='manage_products.php';</script>";
                exit();
            }
        }
        $stmt->close();
    }
} else {
    echo "<script>window.location.href='manage_products.php';</script>";
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = $_POST["id"];
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = trim($_POST["category_id"]);
    $is_featured_new = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller_new = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];

    // Handle image upload
    $new_image_filename = $current_image;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
        $filename = $_FILES["image"]["name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if(in_array($ext, array_keys($allowed))){
            $new_image_filename = "prod_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_image_filename)){
                // New image uploaded successfully
                // Optional: Delete the old image if it's not the default one
                if($current_image != 'default.jpg' && file_exists("../uploads/" . $current_image)){
                    unlink("../uploads/" . $current_image);
                }
            } else {
                 $message = '<div class="alert alert-danger">Image upload failed.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid image format.</div>';
        }
    }

    if(empty($message)){
        $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdisiii", $name, $description, $price, $category_id, $new_image_filename, $is_featured_new, $is_top_seller_new, $product_id);
            if($stmt->execute()){
                echo "<script>window.location.href='manage_products.php';</script>";
                exit();
            } else {
                $message = '<div class="alert alert-danger">Update failed. Please try again.</div>';
            }
            $stmt->close();
        }
    }
}
?>

<h2>Edit Product</h2>
<?php echo $message; ?>
<div class="card shadow">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($current_image); ?>">

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
                    <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" step="0.01">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <div class="mb-2">Current Image: <img src="../uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" style="width: 100px; height: 100px; object-fit: cover;"></div>
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

            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php
// Include the new admin footer
include 'includes/admin_footer.php';
?>
