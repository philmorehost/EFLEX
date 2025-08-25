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

});
