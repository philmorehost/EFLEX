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

// Define variables and initialize
$name = $description = $price = $category_id = "";
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
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller = isset($_POST['is_top_seller']) ? 1 : 0;

    // ... (existing validation logic for name, desc, price, category)

    // Handle image upload
    $image_filename = "";
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
        // ... (existing image upload logic)
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(array_key_exists($ext, $allowed)){
            $new_filename = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $new_filename)){
                $image_filename = $new_filename;
            }
        }
    } else {
        $image_filename = 'default.jpg';
    }

    // Check input errors before inserting in database
    if(empty($name_err) && empty($description_err) && empty($price_err) && empty($category_id_err) && empty($image_err)){

        $sql = "INSERT INTO products (name, description, price, category_id, image, is_featured, is_top_seller) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("ssdisii", $name, $description, $price, $category_id, $image_filename, $is_featured, $is_top_seller);

            if($stmt->execute()){
                header("location: manage_products.php");
                exit();
            } else{
                $message = '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
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
    <title>Add Product</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom_style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar -->
</nav>

<div class="container mt-4">
    <h2>Add New Product</h2>
    <?php echo $message; ?>
    <div class="card">
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo $name; ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control"><?php echo $description; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" name="price" id="price" class="form-control" value="<?php echo $price; ?>" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1">
                    <label class="form-check-label" for="is_featured">Featured Product</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1">
                    <label class="form-check-label" for="is_top_seller">Top Seller</label>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
