<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/transfer" method="post" class="service-form" id="transfer-form">
        <div class="form-group">
            <label for="bank_code">Select Bank</label>
            <select name="bank_code" id="bank_code" class="form-control" required>
                <option value="">-- Loading Banks... --</option>
                <?php foreach ($data['banks'] as $bank): ?>
                    <option value="<?php echo htmlspecialchars($bank['bankCode']); ?>">
                        <?php echo htmlspecialchars($bank['bankName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="account_number">Account Number</label>
            <div class="input-group">
                <input type="text" name="account_number" id="account_number" class="form-control" placeholder="Enter account number" required>
                <button type="button" id="verify-btn" class="btn btn-secondary">Verify</button>
            </div>
            <div id="verify-result" class="verify-result"></div>
        </div>

        <div class="form-group">
            <label for="amount">Amount (₦)</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="e.g., 5000" required disabled>
        </div>

        <div class="form-group">
            <label for="narration">Narration (Optional)</label>
            <input type="text" name="narration" id="narration" class="form-control" placeholder="e.g., For groceries" disabled>
        </div>

        <div id="summary" class="summary-box">
            <p>Amount: <span id="summary-amount">₦0.00</span></p>
            <p>Fee: <span id="summary-fee">₦0.00</span></p>
            <p><strong>Total:</strong> <span id="summary-total"><strong>₦0.00</strong></span></p>
        </div>

        <button type="submit" id="purchase-btn" class="btn btn-primary" disabled>Send Money</button>
    </form>
</div>

<style>
    /* Most styles inherited */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .input-group { display: flex; }
    .btn:disabled { cursor: not-allowed; opacity: 0.65; }
    .verify-result { margin-top: 10px; padding: 10px; border-radius: 4px; display: none; }
    .verify-result.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .verify-result.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .summary-box { background: #f7f7f7; padding: 1rem; border-radius: 5px; border: 1px solid #eee; margin: 1rem 0; display: none; }
    .summary-box p { margin: 0.5rem 0; }
</style>

<script>
const verifyBtn = document.getElementById('verify-btn');
const verifyResult = document.getElementById('verify-result');
const bankSelect = document.getElementById('bank_code');
const accountInput = document.getElementById('account_number');
const amountInput = document.getElementById('amount');
const narrationInput = document.getElementById('narration');
const purchaseBtn = document.getElementById('purchase-btn');
const summaryBox = document.getElementById('summary');
const transferFee = 30.00; // As per Beewave docs

// Function to reset form state when bank or account number changes
function resetFormState() {
    purchaseBtn.disabled = true;
    amountInput.disabled = true;
    narrationInput.disabled = true;
    verifyResult.style.display = 'none';
    summaryBox.style.display = 'none';
}

bankSelect.addEventListener('change', resetFormState);
accountInput.addEventListener('input', resetFormState);

// Handle verification
verifyBtn.addEventListener('click', async function() {
    const bankCode = bankSelect.value;
    const accountNumber = accountInput.value;

    if (!bankCode || !accountNumber) {
        alert('Please select a bank and enter an account number.');
        return;
    }

    this.textContent = 'Verifying...';
    this.disabled = true;

    const formData = new FormData();
    formData.append('bank_code', bankCode);
    formData.append('account_number', accountNumber);

    try {
        const response = await fetch('<?php echo BASE_URL; ?>/transfer/verifyAccount', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            verifyResult.textContent = 'Account Name: ' + result.account_name;
            verifyResult.className = 'verify-result success';
            amountInput.disabled = false;
            narrationInput.disabled = false;
            purchaseBtn.disabled = false;
        } else {
            verifyResult.textContent = 'Error: ' + result.message;
            verifyResult.className = 'verify-result error';
            resetFormState();
        }
    } catch (error) {
        verifyResult.textContent = 'An unexpected error occurred.';
        verifyResult.className = 'verify-result error';
        resetFormState();
    } finally {
        this.textContent = 'Verify';
        this.disabled = false;
        verifyResult.style.display = 'block';
    }
});

// Handle amount input and summary calculation
amountInput.addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    if (amount > 0) {
        const total = amount + transferFee;
        document.getElementById('summary-amount').textContent = '₦' + amount.toFixed(2);
        document.getElementById('summary-fee').textContent = '₦' + transferFee.toFixed(2);
        document.getElementById('summary-total').textContent = '₦' + total.toFixed(2);
        summaryBox.style.display = 'block';
    } else {
        summaryBox.style.display = 'none';
    }
});
</script>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>