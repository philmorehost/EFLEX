// Custom JavaScript for Eflex E-commerce

document.addEventListener('DOMContentLoaded', function() {

    const cartBadge = document.querySelector('.cart-badge');

    // Function to handle the AJAX request
    const addToCart = (productId, quantity) => {
        fetch('ajax_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_to_cart',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update cart count in the header
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                }
                // Optional: Show a success notification/toast
                // For now, we just update the count.
            } else {
                // Handle error
                console.error('Error adding to cart:', data.message);
            }
        })
        .catch(error => console.error('AJAX Error:', error));
    };


    // --- Event listener for simple "Add to Cart" buttons (on product cards) ---
    const addToCartButtons = document.querySelectorAll('.ajax-add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the link from navigating

            const productId = this.dataset.productId;
            const quantity = 1; // Default quantity for card buttons

            addToCart(productId, quantity);
        });
    });


    // --- Event listener for the form on the product detail page ---
    const detailForm = document.getElementById('add-to-cart-form');
    if (detailForm) {
        detailForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            const productId = this.querySelector('input[name="product_id"]').value;
            const quantity = parseInt(this.querySelector('input[name="quantity"]').value, 10);

            addToCart(productId, quantity);
        });
    }

    // AJAX Toggle Wishlist
    document.querySelectorAll('.btn-wishlist').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;

            fetch('ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_wishlist', product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    if(data.action === 'added') {
                        this.classList.add('active');
                    } else {
                        this.classList.remove('active');
                    }
                } else if (data.status === 'login_required') {
                    window.location.href = 'login.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Live Search
    const searchInput = document.getElementById('header-search-input');
    const searchSuggestions = document.getElementById('search-suggestions');

    if(searchInput && searchSuggestions) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            if (query.length < 2) {
                searchSuggestions.innerHTML = '';
                searchSuggestions.style.display = 'none';
                return;
            }

            fetch('ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'product_search', query: query })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success' && data.products.length > 0) {
                    let suggestionsHTML = '';
                    data.products.forEach(product => {
                        suggestionsHTML += `
                            <a href="product_detail.php?id=${product.id}" class="suggestion-item">
                                <img src="uploads/${product.image}" alt="">
                                <div>
                                    <div>${product.name}</div>
                                    <div class="text-muted">${product.price}</div>
                                </div>
                            </a>
                        `;
                    });
                    searchSuggestions.innerHTML = suggestionsHTML;
                    searchSuggestions.style.display = 'block';
                } else {
                    searchSuggestions.innerHTML = '';
                    searchSuggestions.style.display = 'none';
                }
            });
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    }

    // Product Comparison
    const MAX_COMPARE = 4;
    const compareBadge = document.querySelector('.compare-badge');
    let compareItems = JSON.parse(sessionStorage.getItem('compareItems')) || [];

    function updateCompareBadge() {
        if(compareBadge) {
            compareBadge.textContent = compareItems.length;
        }
    }

    document.querySelectorAll('.btn-compare').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;

            const itemIndex = compareItems.indexOf(productId);
            if (itemIndex > -1) {
                // Remove from compare
                compareItems.splice(itemIndex, 1);
                this.classList.remove('active');
            } else {
                // Add to compare
                if(compareItems.length >= MAX_COMPARE) {
                    alert('You can only compare up to ' + MAX_COMPARE + ' products.');
                    return;
                }
                compareItems.push(productId);
                this.classList.add('active');
            }
            sessionStorage.setItem('compareItems', JSON.stringify(compareItems));
            updateCompareBadge();
        });
    });

    // Initialize compare state on page load
    updateCompareBadge();
    document.querySelectorAll('.btn-compare').forEach(button => {
        if(compareItems.includes(button.dataset.productId)){
            button.classList.add('active');
        }
    });
});
