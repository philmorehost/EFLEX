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
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
            <h4 class="text-success"><?php echo format_price($product['price']); ?></h4>

            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>&single=1" class="btn btn-primary btn-lg my-3">Subscribe</a>

            <hr>

            <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>
</div>


<?php
// Include the footer
include 'includes/footer.php';
?>
