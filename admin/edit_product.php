<?php
// Include the new admin header
include 'includes/admin_header.php';
require_permission('manage_products');

$message = "";
$product_id = $_GET['id'] ?? 0;

if(!$product_id) {
    header("location: manage_products.php");
    exit;
}

// Handle Deletion of a gallery image
if(isset($_GET['delete_gallery_image'])) {
    $image_id_to_delete = $_GET['delete_gallery_image'];
    $sql_get_img = "SELECT image_url FROM product_images WHERE id = ? AND product_id = ?";
    $stmt_get = $mysqli->prepare($sql_get_img);
    $stmt_get->bind_param("ii", $image_id_to_delete, $product_id);
    $stmt_get->execute();
    $stmt_get->bind_result($image_url);
    $stmt_get->fetch();
    $stmt_get->close();

    if($image_url) {
        $sql_delete = "DELETE FROM product_images WHERE id = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("i", $image_id_to_delete);
        if($stmt_delete->execute()) {
            if(file_exists('../uploads/' . $image_url)) {
                unlink('../uploads/' . $image_url);
            }
            $message = '<div class="alert alert-success">Gallery image deleted.</div>';
        }
        $stmt_delete->close();
    }
}


// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])){
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);
    $category_id = trim($_POST["category_id"]);
    $is_featured_new = isset($_POST['is_featured']) ? 1 : 0;
    $is_top_seller_new = isset($_POST['is_top_seller']) ? 1 : 0;
    $current_image = $_POST['current_image'];

    // Handle main image upload
    $new_main_image = $current_image;
    if(isset($_FILES["main_image"]) && $_FILES["main_image"]["error"] == 0){
        $filename = $_FILES["main_image"]["name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array($ext, $allowed)){
            $new_filename = "prod_" . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES["main_image"]["tmp_name"], "../uploads/" . $new_filename)){
                $new_main_image = $new_filename;
                if($current_image != 'default.jpg' && file_exists("../uploads/" . $current_image)){
                    unlink("../uploads/" . $current_image);
                }
            }
        }
    }

    // Update product details
    $sql_update = "UPDATE products SET name=?, description=?, price=?, category_id=?, image=?, is_featured=?, is_top_seller=? WHERE id=?";
    $stmt_update = $mysqli->prepare($sql_update);
    $stmt_update->bind_param("ssdisiii", $name, $description, $price, $category_id, $new_main_image, $is_featured_new, $is_top_seller_new, $product_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Handle new gallery image uploads
    if(isset($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
        $sql_gallery = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
        $stmt_gallery = $mysqli->prepare($sql_gallery);
        $image_count = count($_FILES['gallery_images']['name']);
        for($i = 0; $i < $image_count; $i++) {
            if($_FILES['gallery_images']['error'][$i] == 0) {
                $filename = $_FILES['gallery_images']['name'][$i];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if(in_array($ext, $allowed)){
                    $new_gallery_filename = "prod_gallery_" . uniqid() . "." . $ext;
                    if(move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], "../uploads/" . $new_gallery_filename)){
                        $stmt_gallery->bind_param("is", $product_id, $new_gallery_filename);
                        $stmt_gallery->execute();
                    }
                }
            }
        }
        $stmt_gallery->close();
    }
    $message = '<div class="alert alert-success">Product updated successfully.</div>';
}

// Fetch product data for the form
$sql_product = "SELECT * FROM products WHERE id = ?";
$stmt_product = $mysqli->prepare($sql_product);
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$product = $stmt_product->get_result()->fetch_assoc();
$stmt_product->close();

// Fetch gallery images
$sql_gallery_fetch = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
$stmt_gallery_fetch = $mysqli->prepare($sql_gallery_fetch);
$stmt_gallery_fetch->bind_param("i", $product_id);
$stmt_gallery_fetch->execute();
$gallery_images = $stmt_gallery_fetch->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_gallery_fetch->close();

// Fetch categories
$sql_categories = "SELECT * FROM categories ORDER BY name ASC";
$result_categories = $mysqli->query($sql_categories);
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
?>

<h2>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
<?php echo $message; ?>

<div class="card shadow mb-4">
    <div class="card-header">Product Details</div>
    <div class="card-body">
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="price" class="form-label">Price</label><input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01"></div>
                <div class="col-md-6 mb-3"><label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="main_image" class="form-label">Main Product Image</label>
                <div class="mb-2"><img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" style="width: 100px; height: 100px; object-fit: cover;"></div>
                <input type="file" name="main_image" id="main_image" class="form-control">
                <div class="form-text">Upload a new file to replace the current main image.</div>
            </div>
            <div class="mb-3 form-check"><input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo ($product['is_featured']) ? 'checked' : ''; ?>><label class="form-check-label" for="is_featured">Featured</label></div>
            <div class="mb-3 form-check"><input type="checkbox" name="is_top_seller" class="form-check-input" id="is_top_seller" value="1" <?php echo ($product['is_top_seller']) ? 'checked' : ''; ?>><label class="form-check-label" for="is_top_seller">Top Seller</label></div>
            <button type="submit" name="update_product" class="btn btn-primary">Update Product Details</button>
            <a href="manage_products.php" class="btn btn-secondary">Back to Products</a>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">Product Gallery</div>
    <div class="card-body">
        <div class="row">
            <?php foreach($gallery_images as $img): ?>
            <div class="col-md-3 text-center mb-3">
                <img src="../uploads/<?php echo htmlspecialchars($img['image_url']); ?>" class="img-fluid mb-2" style="height: 150px; object-fit: cover;">
                <a href="edit_product.php?id=<?php echo $product_id; ?>&delete_gallery_image=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
            </div>
            <?php endforeach; ?>
        </div>
        <hr>
        <h5>Add New Gallery Images</h5>
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
             <div class="mb-3">
                <label for="gallery_images" class="form-label">New Images</label>
                <input type="file" name="gallery_images[]" id="gallery_images" class="form-control" multiple>
            </div>
            <button type="submit" name="update_product" class="btn btn-info">Upload New Images</button>
        </form>
    </div>
</div>

<?php
// Include the new admin footer
include 'includes/admin_footer.php';
?>
