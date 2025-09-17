<?php
// The controller now handles fetching the $product data.
require_once __DIR__ . '/partials/header.php';
?>

<div class="product-details-layout">
    <div class="product-main-content">
        <div class="product-header">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-category-detail">in <a href="/products?category_id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></p>
        </div>

        <div class="product-gallery">
            <img src="https://via.placeholder.com/800x500" alt="Product Main Image" class="main-image">
        </div>

        <div class="product-description">
            <h2>Product Description</h2>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>

        <div class="product-reviews">
            <h2>Customer Reviews</h2>
            <p>No reviews yet.</p>
        </div>
    </div>

    <aside class="product-purchase-sidebar">
        <div class="purchase-box">
            <div class="price-tag">$<?php echo htmlspecialchars($product['price']); ?></div>
            <form action="/cart/add" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn btn-success btn-block">Add to Cart</button>
            </form>
            <button class="btn btn-primary btn-block" style="margin-top: 10px;">Buy Now</button>
        </div>
        <div class="seller-info">
            <h4>Sold By</h4>
            <a href="#">Seller Name</a>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
