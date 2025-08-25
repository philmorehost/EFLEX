<?php
// We need to start the session on all pages to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    $_SESSION['redirect_to'] = 'checkout.php';
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';

// Check if the cart is empty, if so, redirect to cart page
if(empty($_SESSION['cart'])){
    header("location: cart.php");
    exit;
}

// Fetch cart items and calculate total price
$cart_items = [];
$total_price = 0;
if(!empty($_SESSION['cart'])){
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    if($stmt = $mysqli->prepare($sql)){
        $types = str_repeat('i', count($product_ids));
        $stmt->bind_param($types, ...$product_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $product_id = $row['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $subtotal = $row['price'] * $quantity;
            $total_price += $subtotal;
            $cart_items[$product_id] = ['name' => $row['name'], 'price' => $row['price'], 'quantity' => $quantity, 'subtotal' => $subtotal];
        }
        $stmt->close();
    }
}

// NOTE: The server-side order processing logic has been removed from this file.
// It will be moved to a separate endpoint that is called after successful payment.

// Include config for API keys
require_once 'includes/config.php';

// Include the header
include 'includes/header.php';
?>
<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<h2>Checkout</h2>
<div class="row">
    <!-- Order Summary -->
    <div class="col-md-5 col-lg-4 order-md-last">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your cart</span>
            <span class="badge bg-primary rounded-pill"><?php echo count($cart_items); ?></span>
        </h4>
        <ul class="list-group mb-3">
            <?php foreach($cart_items as $item): ?>
            <li class="list-group-item d-flex justify-content-between lh-sm">
                <div>
                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                </div>
                <span class="text-muted">$<?php echo number_format($item['subtotal'], 2); ?></span>
            </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between">
                <span>Total (USD)</span>
                <strong>$<?php echo number_format($total_price, 2); ?></strong>
            </li>
        </ul>
    </div>

    <!-- Payment Form -->
    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Shipping & Payment</h4>
        <form id="payment-form">
            <!-- Shipping Address -->
            <h5 class="mb-3">Shipping address</h5>
            <div class="row g-3">
                <div class="col-12"><label for="fullName" class="form-label">Full name</label><input type="text" class="form-control" id="fullName" name="fullName" required></div>
                <div class="col-12"><label for="address" class="form-label">Address</label><input type="text" class="form-control" id="address" name="address" required></div>
            </div>
            <hr class="my-4">

            <!-- Payment Element -->
            <h5 class="mb-3">Payment</h5>
            <div id="payment-element">
                <!-- Stripe.js injects the Payment Element here -->
            </div>

            <button id="submit" class="w-100 btn btn-primary btn-lg mt-4">
                <div class="spinner-border spinner-border-sm d-none" id="spinner" role="status"></div>
                <span id="button-text">Pay now</span>
            </button>
            <div id="payment-message" class="text-danger mt-2"></div>
        </form>
    </div>
</div>

<script>
    // NOTE: The following client-side code is for scaffolding purposes.
    // It requires a server-side endpoint (e.g., 'create_payment_intent.php') to fetch a real clientSecret.
    // This server-side endpoint needs the Stripe PHP SDK, which could not be installed in the current environment.

    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

    // Fetch the client secret, then initialize Stripe Elements and add event listeners
    fetch('create_payment_intent.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            const elements = stripe.elements({
                clientSecret: data.clientSecret,
                appearance: { theme: 'stripe' }
            });

            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('button-text');
            const paymentMessage = document.getElementById('payment-message');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                buttonText.textContent = 'Processing...';

                const { error } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: window.location.origin + '/order_success.php',
                    },
                });

                if (error) {
                    paymentMessage.textContent = error.message;
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                    buttonText.textContent = 'Pay now';
                }
            });
        })
        .catch(error => {
            console.error('Error fetching client secret:', error);
            const paymentMessage = document.getElementById('payment-message');
            paymentMessage.textContent = 'Error initializing payment form. Please try again later.';
        });
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
