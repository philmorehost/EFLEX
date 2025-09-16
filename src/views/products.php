<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="page-header">
    <h1>Browse All Products</h1>
    <div class="search-bar">
        <input type="text" placeholder="Search for products...">
        <button class="btn">Search</button>
    </div>
</div>

<div class="products-page-layout">
    <aside class="filters-sidebar">
        <h3>Filters</h3>
        <div class="filter-group">
            <h4>Category</h4>
            <ul>
                <li><a href="#">PHP Scripts</a></li>
                <li><a href="#">WordPress</a></li>
                <li><a href="#">JavaScript</a></li>
                <li><a href="#">HTML & CSS</a></li>
                <li><a href="#">Mobile Apps</a></li>
            </ul>
        </div>
        <div class="filter-group">
            <h4>Price Range</h4>
            <input type="range" min="0" max="1000" value="500">
            <span>$0 - $1000</span>
        </div>
        <div class="filter-group">
            <h4>Rating</h4>
            <select>
                <option>4 Stars & Up</option>
                <option>3 Stars & Up</option>
                <option>2 Stars & Up</option>
                <option>1 Star & Up</option>
            </select>
        </div>
    </aside>

    <main class="products-grid-container">
        <div class="product-grid">
            <!-- Placeholder Product Card -->
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Awesome Script</h3>
                    <p class="product-category">PHP Scripts</p>
                    <div class="product-price">$59</div>
                </div>
            </div>
            <!-- Repeat for other products -->
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Modern Theme</h3>
                    <p class="product-category">WordPress</p>
                    <div class="product-price">$49</div>
                </div>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Powerful Plugin</h3>
                    <p class="product-category">JavaScript</p>
                    <div class="product-price">$29</div>
                </div>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Creative Template</h3>
                    <p class="product-category">HTML & CSS</p>
                    <div class="product-price">$19</div>
                </div>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Mobile App UI Kit</h3>
                    <p class="product-category">Mobile Apps</p>
                    <div class="product-price">$39</div>
                </div>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/300x200" alt="Product Name">
                <div class="product-card-content">
                    <h3>Another Great Script</h3>
                    <p class="product-category">PHP Scripts</p>
                    <div class="product-price">$79</div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
