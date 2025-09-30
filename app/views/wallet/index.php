<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="wallet-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('wallet_error'); ?>

    <div class="funding-options">

        <!-- Section for Bank Transfer via Virtual Account -->
        <div class="funding-card" id="bank-transfer">
            <h3>Pay via Bank Transfer</h3>
            <?php if (isset($data['virtual_account']) && $data['virtual_account']) : ?>
                <p>A dedicated bank account has been reserved for you. Transfer to the account below to fund your wallet instantly.</p>
                <div class="account-details">
                    <p><strong>Bank Name:</strong> <span><?php echo htmlspecialchars($data['virtual_account']->bank_name); ?></span></p>
                    <p><strong>Account Number:</strong> <span><?php echo htmlspecialchars($data['virtual_account']->account_number); ?></span></p>
                    <p><strong>Account Name:</strong> <span><?php echo htmlspecialchars($data['virtual_account']->account_name); ?></span></p>
                </div>
                <small>Note: A ₦50 stamp duty charge will be applied on deposits of ₦10,000 and above.</small>
            <?php else: ?>
                <p class="error">Could not retrieve your funding account details at this time. This might be due to a temporary issue or incomplete payment settings by the administrator. Please try again later or contact support.</p>
            <?php endif; ?>
        </div>

        <!-- Section for Card Payments -->
        <div class="funding-card" id="card-payment">
            <h3>Pay with Card or Bank (Paystack)</h3>
            <p>Enter the amount you wish to deposit and click the button below to pay securely with Paystack.</p>
            <form id="paystack-form">
                <div class="form-group">
                    <label for="paystack-amount">Amount (₦)</label>
                    <input type="number" id="paystack-amount" class="form-control" placeholder="e.g., 500" required>
                </div>
                <button type="submit" class="btn btn-primary">Pay with Paystack</button>
            </form>
        </div>

        <!-- Section for Flutterwave Payments -->
        <div class="funding-card" id="flutterwave-payment">
            <h3>Pay with Card or Bank (Flutterwave)</h3>
            <p>Enter the amount you wish to deposit and click the button below to pay securely with Flutterwave.</p>
            <form id="flutterwave-form">
                <div class="form-group">
                    <label for="flutterwave-amount">Amount (₦)</label>
                    <input type="number" id="flutterwave-amount" class="form-control" placeholder="e.g., 500" required>
                </div>
                <button type="submit" class="btn btn-primary">Pay with Flutterwave</button>
            </form>
        </div>

    </div>
</div>

<style>
    .wallet-container {
        width: 90%;
        max-width: 900px;
        margin: 20px auto;
    }
    .funding-options {
        margin-top: 2rem;
        display: grid;
        gap: 2rem;
    }
    .funding-card {
        background: #fff;
        padding: 2rem;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .funding-card h3 {
        margin-top: 0;
        color: #4a90e2;
    }
    .account-details {
        background: #f7f7f7;
        padding: 1rem;
        border-radius: 5px;
        border: 1px solid #eee;
        margin: 1rem 0;
    }
    .account-details p {
        margin: 0.5rem 0;
        font-size: 1.1em;
    }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-control { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .btn { display: inline-block; font-weight: 400; padding: .5rem 1rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
    .btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
    .error { color: #dc3545; }
    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
</style>

<!-- Paystack and Flutterwave JS Libraries -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>

<script>
// Paystack Integration
const paystackForm = document.getElementById('paystack-form');
paystackForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const amount = document.getElementById('paystack-amount').value;
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }

    const handler = PaystackPop.setup({
        key: '<?php echo $data['paystack_public_key']; ?>',
        email: '<?php echo $data['user_email']; ?>',
        amount: amount * 100, // Paystack expects amount in kobo
        ref: 'VTU-' + Math.floor((Math.random() * 1000000000) + 1),
        callback: function(response) {
            // The webhook is the primary source of truth, but we can redirect to a success page here.
            // A more robust solution would be to verify the transaction server-side here via AJAX.
            alert('Payment successful! Your wallet will be credited shortly. Ref: ' + response.reference);
            window.location.href = '<?php echo BASE_URL; ?>/dashboard';
        },
        onClose: function() {
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
});

// Flutterwave Integration
const flutterwaveForm = document.getElementById('flutterwave-form');
flutterwaveForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const amount = document.getElementById('flutterwave-amount').value;
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }

    FlutterwaveCheckout({
        public_key: '<?php echo $data['flutterwave_public_key']; ?>',
        tx_ref: 'VTU-FLW-' + Math.floor((Math.random() * 1000000000) + 1),
        amount: amount,
        currency: "NGN",
        payment_options: "card, banktransfer",
        customer: {
            email: '<?php echo $data['user_email']; ?>',
            phone_number: '<?php echo $data['user_phone']; ?>',
            name: '<?php echo $data['user_name']; ?>',
        },
        customizations: {
            title: '<?php echo SITE_NAME; ?> Wallet Funding',
            description: 'Payment for wallet top-up',
            logo: '', // Add a logo URL here if you have one
        },
        callback: function(response) {
            // Again, webhook is primary truth. This callback can be used for immediate feedback.
            if (response.status == "successful") {
                alert('Payment successful! Your wallet will be credited shortly.');
                window.location.href = '<?php echo BASE_URL; ?>/dashboard';
            } else {
                alert('Payment was not successful.');
            }
        },
        onclose: function() {
            console.log('Flutterwave checkout closed.');
        }
    });
});
</script>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>