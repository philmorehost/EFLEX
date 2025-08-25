<?php
// Include the header
include 'includes/header.php';

// Check if product ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<h1>Product not found</h1>";
    include 'includes/footer.php';
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?";
$product = null;
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1){
        $product = $result->fetch_assoc();
    }
    $stmt->close();
}

// If product not found, display message
if(!$product){
    echo "<h1>Product not found</h1>";
    include 'includes/footer.php';
    exit();
}

// Fetch gallery images
$sql_gallery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC";
$stmt_gallery = $mysqli->prepare($sql_gallery);
$stmt_gallery->bind_param("i", $product_id);
$stmt_gallery->execute();
$gallery_images = $stmt_gallery->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_gallery->close();

?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="main-image-container mb-3">
                 <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid w-100" id="mainProductImage" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <?php if(count($gallery_images) > 0): ?>
            <div class="product-thumbnails d-flex gap-2">
                <!-- Main image as first thumbnail -->
                <div class="thumbnail-item">
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid" alt="Thumbnail" onclick="changeMainImage(this)">
                </div>
                <!-- Gallery images -->
                <?php foreach($gallery_images as $img): ?>
                <div class="thumbnail-item">
                    <img src="uploads/<?php echo htmlspecialchars($img['image_url']); ?>" class="img-fluid" alt="Thumbnail" onclick="changeMainImage(this)">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <h4 class="text-success mb-3"><?php echo htmlspecialchars($_SESSION['currency_symbol']); ?><?php echo htmlspecialchars($product['price']); ?></h4>

            <form id="add-to-cart-form" class="ajax-add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-8">
                         <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                    </div>
                </div>
            </form>

            <div class="product-share mt-4">
                <span class="me-2">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode('Check out this product: ' . $product['name']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $product['image']); ?>&description=<?php echo urlencode($product['name']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fab fa-pinterest"></i></a>
            </div>

            <hr class="my-4">

            <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>
</div>

<style>
.product-thumbnails {
    overflow-x: auto;
}
.thumbnail-item {
    flex: 0 0 80px; /* Do not grow, do not shrink, initial width 80px */
    cursor: pointer;
    border: 2px solid transparent;
    padding: 2px;
}
.thumbnail-item:hover, .thumbnail-item.active {
    border-color: var(--woodmart-primary-color);
}
.main-image-container {
    border: 1px solid #eee;
}
</style>

<script>
function changeMainImage(thumbnail) {
    const mainImage = document.getElementById('mainProductImage');
    mainImage.src = thumbnail.src;

    // Optional: Add active state to thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    thumbnails.forEach(item => item.classList.remove('active'));
    thumbnail.parentElement.classList.add('active');
}
// Set the first thumbnail as active initially
document.addEventListener('DOMContentLoaded', function() {
    const firstThumbnail = document.querySelector('.thumbnail-item');
    if (firstThumbnail) {
        firstThumbnail.classList.add('active');
    }
});
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
