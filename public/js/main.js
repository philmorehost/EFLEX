$(document).ready(function() {

    // Check if we are on the products page by looking for the search form
    if ($('#search-form').length) {

        function renderProducts(products) {
            const grid = $('#product-grid');
            grid.empty();

            if (products.length === 0) {
                grid.html('<p>No products found matching your criteria.</p>');
                return;
            }

            products.forEach(function(product) {
                const productCard = `
                    <div class="product-card">
                        <a href="/product-details?id=${product.id}">
                            <img src="https://via.placeholder.com/300x200" alt="${product.name}">
                            <div class="product-card-content">
                                <h3>${product.name}</h3>
                                <p class="product-category">${product.category_name}</p>
                                <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                            </div>
                        </a>
                    </div>
                `;
                grid.append(productCard);
            });
        }

        function fetchProducts() {
            const criteria = {
                keyword: $('#keyword-input').val(),
                category_id: $('#category-filter').val(),
                min_rating: $('#rating-filter').val()
            };

            $('#product-grid').html('<p>Loading products...</p>');

            $.ajax({
                url: '/api/search',
                type: 'GET',
                data: criteria,
                dataType: 'json',
                success: function(products) {
                    renderProducts(products);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    $('#product-grid').html('<p>An error occurred while fetching products.</p>');
                }
            });
        }

        // --- Event Listeners ---
        let typingTimer;
        const doneTypingInterval = 500;

        $('#keyword-input').on('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(fetchProducts, doneTypingInterval);
        });

        $('#keyword-input').on('keydown', function() {
            clearTimeout(typingTimer);
        });

        $('#category-filter, #rating-filter').on('change', function() {
            fetchProducts();
        });

        // Initial fetch
        fetchProducts();
    }
});
