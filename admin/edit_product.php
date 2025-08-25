<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Include database connection file
require_once "../includes/db_connect.php";

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
                header("location: manage_products.php");
                exit();
            }
        }
        $stmt->close();
    }
} else {
    header("location: manage_products.php");
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
    $current_image = $_POST['current_image']; // Get current image from hidden input

    // Handle image upload
    $new_image_filename = $current_image;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        // ... (image upload logic) ...
        $new_image_filename = uniqid() . '_' . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_image_filename);
    }

    $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("ssdisiii", $name, $description, $price, $category_id, $new_image_filename, $is_featured_new, $is_top_seller_new, $product_id);
        if($stmt->execute()){
            header("location: manage_products.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger">Update failed.</div>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom_style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar -->
</nav>

<div class="container mt-4">
    <h2>Edit Product</h2>
    <?php echo $message; ?>
    <div class="card">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <p>Current Image: <img src="../uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" style="width: 100px;"></p>
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
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
