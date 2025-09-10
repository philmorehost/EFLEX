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

    // --- Mobile Navigation Toggle ---
    const navToggleBtn = document.getElementById('nav-toggle-btn');
    const mainNav = document.getElementById('main-nav');

    if (navToggleBtn && mainNav) {
        navToggleBtn.addEventListener('click', function() {
            mainNav.classList.toggle('is-active');
        });
    }

    // --- Mobile Dropdown Toggle ---
    const dropdownToggles = document.querySelectorAll('.nav-item-dropdown > a');
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            // We only want this behavior on mobile
            if (window.innerWidth < 992) {
                e.preventDefault(); // Prevent link navigation
                const parentDropdown = toggle.parentElement;
                parentDropdown.classList.toggle('is-open');
            }
        });
    });

});

// --- PWA Installation Prompt ---
if (window.pwaEnabled) {
    let deferredPrompt;
    const pwaInstallModalEl = document.getElementById('pwa-install-modal');
    const pwaInstallModal = pwaInstallModalEl ? new bootstrap.Modal(pwaInstallModalEl) : null;
    const installButton = document.getElementById('pwa-install-button');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        // Show the custom install prompt.
        if (pwaInstallModal) {
            pwaInstallModal.show();
        }
    });

    if (installButton) {
        installButton.addEventListener('click', async () => {
            // Hide the app provided install promotion
            if (pwaInstallModal) {
                pwaInstallModal.hide();
            }
            // Show the install prompt
            if (deferredPrompt) {
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                // We've used the prompt, and can't use it again, throw it away
                deferredPrompt = null;
            }
        });
    }

    window.addEventListener('appinstalled', () => {
        // Hide the install prompt
        if (pwaInstallModal) {
            pwaInstallModal.hide();
        }
        deferredPrompt = null;
        console.log('PWA was installed');
    });
}
