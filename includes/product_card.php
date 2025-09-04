<div class="card h-100">
    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
    </a>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title"><a href="product_detail.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($product['name']); ?></a></h5>
        <p class="card-text text-muted"><?php echo format_price($product['price']); ?></p>
        <div class="mt-auto">
             <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary ajax-add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</a>
        </div>
    </div>
</div>
