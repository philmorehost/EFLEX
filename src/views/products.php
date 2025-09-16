<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="page-header">
    <h1>Browse All Products</h1>
    <form id="search-form">
        <div class="search-bar">
            <input type="text" id="keyword-input" class="filter-control" placeholder="Search for products...">
        </div>
    </form>
</div>

<div class="products-page-layout">
    <aside class="filters-sidebar">
        <h3>Filters</h3>
        <div class="filter-group">
            <h4>Category</h4>
            <select id="category-filter" class="filter-control">
                <option value="">All Categories</option>
                <!-- In a real app, this would be populated from the DB -->
                <option value="1">PHP Scripts</option>
                <option value="2">WordPress</option>
                <option value="3">JavaScript</option>
                <option value="4">HTML & CSS</option>
            </select>
        </div>
        <div class="filter-group">
            <h4>Rating</h4>
            <select id="rating-filter" class="filter-control">
                <option value="">Any Rating</option>
                <option value="4">4 Stars & Up</option>
                <option value="3">3 Stars & Up</option>
                <option value="2">2 Stars & Up</option>
                <option value="1">1 Star & Up</option>
            </select>
        </div>
    </aside>

    <main class="products-grid-container">
        <div id="product-grid" class="product-grid">
            <!-- Products will be dynamically inserted here by JavaScript -->
            <p>Loading products...</p>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
