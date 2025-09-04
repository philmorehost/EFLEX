<?php
// Include the header
include 'includes/header.php';

// Check for a freemium success flag
$is_freemium = false;
if (isset($_SESSION['freemium_success'])) {
    $is_freemium = true;
    unset($_SESSION['freemium_success']);
}
?>

<div class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="message-container">
                <?php if ($is_freemium): ?>
                    <div class="card border-success">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="card-title">Success!</h4>
                            <p class="card-text">Your freemium subscription is now active.</p>
                            <hr>
                            <p class="mb-0">Your Order ID is: <strong>#<?php echo htmlspecialchars($_GET['order_id'] ?? 'N/A'); ?></strong></p>
                        </div>
                        <div class="card-footer">
                             <a href="lesson_notes.php" class="btn btn-success me-2">Go to Lesson Notes</a>
                             <a href="index.php" class="btn btn-outline-primary">Continue Shopping</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="mt-3">Verifying your payment...</h4>
                    <p class="text-muted">Please do not close this window. This may take a moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only run the payment verification if it's not a freemium success
    <?php if (!$is_freemium): ?>
        const messageContainer = document.getElementById('message-container');
        const urlParams = new URLSearchParams(window.location.search);

        // This is still the old Stripe logic, but we leave it for now
        // as the main task was to implement Freemium and Paystack.
        const paymentIntentId = urlParams.get('payment_intent');
        if (paymentIntentId) {
            fetch('finalize_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ payment_intent: paymentIntentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageContainer.innerHTML = `
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Thank You!</h4>
                            <p>Your order has been placed successfully.</p>
                            <hr>
                            <p class="mb-0">Your Order ID is: <strong>${data.orderId}</strong></p>
                        </div>
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>`;
                } else {
                     messageContainer.innerHTML = `<div class="alert alert-danger"><h4>Order Failed</h4><p>${data.message || 'An unknown error occurred.'}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageContainer.innerHTML = `<div class="alert alert-danger"><h4>Network Error</h4><p>Could not finalize your order.</p></div>`;
            });
        } else {
             messageContainer.innerHTML = `<div class="alert alert-danger"><h4>Invalid Access</h4><p>No payment information found.</p></div>`;
        }
    <?php endif; ?>
});
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
