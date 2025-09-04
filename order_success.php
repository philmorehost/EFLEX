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

        // Check for Paystack reference
        const paystackRef = urlParams.get('reference');

        if (paystackRef) {
            // We have a Paystack reference, let's verify it
            fetch('finalize_paystack_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reference: paystackRef })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageContainer.innerHTML = `
                        <div class="card border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4 class="card-title">Thank You!</h4>
                                <p class="card-text">Your order has been placed successfully.</p>
                                <hr>
                                <p class="mb-0">Your Order ID is: <strong>${data.orderId}</strong></p>
                            </div>
                            <div class="card-footer">
                                 <a href="account.php" class="btn btn-success me-2">Go to My Account</a>
                                 <a href="index.php" class="btn btn-outline-primary">Continue Shopping</a>
                            </div>
                        </div>`;
                } else {
                    messageContainer.innerHTML = `
                        <div class="card border-danger">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h4 class="card-title">Payment Failed</h4>
                                <p class="card-text">There was a problem processing your payment.</p>
                                <p class="mb-0 text-danger"><small>Details: ${data.message || 'An unknown error occurred.'}</small></p>
                            </div>
                             <div class="card-footer">
                                 <a href="subscriptions.php" class="btn btn-danger">Try Again</a>
                            </div>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageContainer.innerHTML = `
                     <div class="card border-danger">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                            <h4 class="card-title">Network Error</h4>
                            <p class="card-text">A network error occurred while finalizing your order. Please check your order history or contact support.</p>
                        </div>
                    </div>`;
            });
        } else {
            // Handle cases where no payment reference is found
            messageContainer.innerHTML = `
                 <div class="card border-warning">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                        <h4 class="card-title">Invalid Access</h4>
                        <p class="card-text">No payment information was found. Your order cannot be confirmed this way.</p>
                    </div>
                    <div class="card-footer">
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>`;
        }
    <?php endif; ?>
});
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
