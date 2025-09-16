<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="product-details-layout">
    <div class="product-main-content">
        <div class="product-header">
            <h1>Awesome Script</h1>
            <p class="product-category-detail">in <a href="#">PHP Scripts</a></p>
        </div>

        <div class="product-gallery">
            <img src="https://via.placeholder.com/800x500" alt="Product Main Image" class="main-image">
            <div class="thumbnail-images">
                <img src="https://via.placeholder.com/100x70" alt="Thumbnail 1">
                <img src="https://via.placeholder.com/100x70" alt="Thumbnail 2" class="active">
                <img src="https://via.placeholder.com/100x70" alt="Thumbnail 3">
            </div>
        </div>

        <div class="product-description">
            <h2>Product Description</h2>
            <p>This is a detailed description of the "Awesome Script". It's a powerful and flexible solution for all your needs. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet.</p>
            <h3>Key Features</h3>
            <ul>
                <li>Feature One: Does amazing things.</li>
                <li>Feature Two: Incredibly easy to use.</li>
                <li>Feature Three: Well-documented and supported.</li>
                <li>Feature Four: Fully responsive design.</li>
            </ul>
        </div>

        <div class="product-reviews">
            <h2>Customer Reviews (3)</h2>
            <div class="review">
                <div class="review-author">John Doe</div>
                <div class="review-rating">★★★★☆</div>
                <p class="review-comment">Great script! Very easy to customize and the support is top-notch. Highly recommended.</p>
            </div>
            <div class="review">
                <div class="review-author">Jane Smith</div>
                <div class="review-rating">★★★★★</div>
                <p class="review-comment">Absolutely fantastic. Saved me weeks of development time. Worth every penny!</p>
            </div>
        </div>
    </div>

    <aside class="product-purchase-sidebar">
        <div class="purchase-box">
            <div class="price-tag">$59</div>
            <p>Choose a license:</p>
            <select>
                <option>Regular License</option>
                <option>Extended License</option>
            </select>
            <button class="btn btn-success btn-block">Add to Cart</button>
            <button class="btn btn-primary btn-block">Buy Now</button>
        </div>
        <div class="seller-info">
            <h4>Sold By</h4>
            <a href="#">TopSeller</a>
            <p>Member since 2023</p>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
