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

// Fetch categories for the dropdown
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Define variables and initialize with empty values
$name = $description = $price = $category_id = $current_image = "";
$name_err = $description_err = $price_err = $category_id_err = $image_err = "";
$message = "";
$product_id = 0;

// Check if ID is provided for editing
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $product_id = trim($_GET["id"]);

    // Prepare a select statement
    $sql = "SELECT name, description, price, category_id, image FROM products WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $product_id);
        if($stmt->execute()){
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($name, $description, $price, $category_id, $current_image);
                $stmt->fetch();
            } else{
                // URL doesn't contain valid ID. Redirect to error page or product list
                header("location: manage_products.php");
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
} else {
    // URL doesn't contain ID parameter. Redirect to error page or product list
    header("location: manage_products.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $product_id = $_POST["id"]; // Get ID from hidden form field

    // Validate name, desc, price, category... (same as add_product.php)
    // ... validation logic ...
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = trim($_POST["category_id"]);


    // Handle image upload
    $new_image_filename = $current_image;
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        // ... image validation and upload logic (same as add_product.php) ...
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(array_key_exists($ext, $allowed) && $filesize < 5 * 1024 * 1024 && in_array($filetype, $allowed)){
            $new_filename_base = uniqid();
            $new_image_filename = $new_filename_base . "." . $ext;
            if(move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_image_filename)){
                // New image uploaded successfully, delete old one if it's not the default
                if($current_image && $current_image != 'default.jpg' && file_exists("../uploads/" . $current_image)){
                    unlink("../uploads/" . $current_image);
                }
            } else {
                $image_err = "Error uploading file.";
            }
        } else {
            $image_err = "Invalid file format or size.";
        }
    }

    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err)){
        $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, image=? WHERE id=?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdisi", $name, $description, $price, $category_id, $new_image_filename, $product_id);
            if($stmt->execute()){
                header("location: manage_products.php");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Update failed. Please try again.</div>';
            }
            $stmt->close();
        }
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
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar content -->
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
         <div class="collapse navbar-collapse">
             <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="manage_products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_categories.php">Categories</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Edit Product</h2>
    <?php echo $message; ?>
    <div class="card">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product_id; ?>">

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

                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
