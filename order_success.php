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
                             <a href="products.php" class="btn btn-outline-primary">Browse More Classes</a>
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
        const reference = urlParams.get('reference');
        const order_id = urlParams.get('order_id');
        const user_id = urlParams.get('user_id');

        if (reference && order_id && user_id) {
            fetch('finalize_paystack_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    reference: reference,
                    order_id: order_id,
                    user_id: user_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageContainer.innerHTML = `
                        <div class="card border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4 class="card-title">Thank You!</h4>
                                <p class="card-text">Your payment was successful and your subscription is now active.</p>
                                <hr>
                                <p class="mb-0">Your Order ID is: <strong>#${data.orderId}</strong></p>
                            </div>
                            <div class="card-footer">
                                <a href="lesson_notes.php" class="btn btn-success me-2">Go to Lesson Notes</a>
                                <a href="products.php" class="btn btn-outline-primary">Browse More Classes</a>
                            </div>
                        </div>`;
                } else {
                     messageContainer.innerHTML = `
                        <div class="card border-danger">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h4 class="card-title">Payment Failed</h4>
                                <p class="card-text">${data.message || 'An unknown error occurred during payment verification.'}</p>
                                <hr>
                                <p class="text-muted">Please contact support if you believe this is an error.</p>
                            </div>
                            <div class="card-footer">
                                <a href="cart.php" class="btn btn-danger">Return to Your Classes</a>
                            </div>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageContainer.innerHTML = `<div class="alert alert-danger"><h4>Network Error</h4><p>Could not finalize your order. Please check your internet connection and contact support.</p></div>`;
            });
        } else {
             messageContainer.innerHTML = `<div class="alert alert-warning"><h4>Invalid Access</h4><p>No payment reference found. If you have completed a payment, please contact support.</p></div>`;
        }
    <?php endif; ?>
});
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
