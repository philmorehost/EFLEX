<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/electricity" method="post" class="service-form" id="electric-form">
        <div class="form-group">
            <label for="provider">Select Provider</label>
            <select name="provider" id="provider" class="form-control" required>
                <option value="">-- Select Provider --</option>
                <option value="ikedc">IKEDC</option>
                <option value="ekedc">EKEDC</option>
                <option value="aedc">AEDC</option>
                <option value="jedc">JEDC</option>
                <option value="ibedc">IBEDC</option>
                <option value="kedco">KEDCO</option>
                <option value="phed">PHED</option>
                <option value="eedc">EEDC</option>
                <option value="yedc">YEDC</option>
            </select>
        </div>

        <div class="form-group">
            <label for="meter_number">Meter Number</label>
            <div class="input-group">
                <input type="text" name="meter_number" id="meter_number" class="form-control" placeholder="Enter meter number" required>
                <button type="button" id="verify-btn" class="btn btn-secondary">Verify</button>
            </div>
            <div id="verify-result" class="verify-result"></div>
        </div>

        <div class="form-group">
            <label for="type">Meter Type</label>
            <select name="type" id="type" class="form-control" required disabled>
                <option value="">-- Verify meter first --</option>
                <option value="prepaid">Prepaid</option>
                <option value="postpaid">Postpaid</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount (₦)</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="e.g., 2000" required disabled>
        </div>

        <button type="submit" id="purchase-btn" class="btn btn-primary" disabled>Pay Bill</button>
    </form>
</div>

<style>
    /* Styles are inherited */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .input-group { display: flex; }
    .input-group .form-control { border-top-right-radius: 0; border-bottom-right-radius: 0; }
    .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .btn:disabled { cursor: not-allowed; opacity: 0.65; }
    .verify-result { margin-top: 10px; padding: 10px; border-radius: 4px; display: none; }
    .verify-result.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .verify-result.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<script>
const verifyBtn = document.getElementById('verify-btn');
const verifyResult = document.getElementById('verify-result');
const meterInput = document.getElementById('meter_number');
const providerSelect = document.getElementById('provider');
const typeSelect = document.getElementById('type');
const amountInput = document.getElementById('amount');
const purchaseBtn = document.getElementById('purchase-btn');

verifyBtn.addEventListener('click', async function() {
    const provider = providerSelect.value;
    const meterNumber = meterInput.value;

    if (!provider || !meterNumber) {
        alert('Please select a provider and enter a meter number.');
        return;
    }

    this.textContent = 'Verifying...';
    this.disabled = true;
    verifyResult.style.display = 'none';
    purchaseBtn.disabled = true;
    typeSelect.disabled = true;
    amountInput.disabled = true;

    const formData = new FormData();
    formData.append('provider', provider);
    formData.append('meter_number', meterNumber);

    try {
        const response = await fetch('<?php echo BASE_URL; ?>/services/verifyElectric', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            verifyResult.textContent = 'Customer: ' + result.customer_name;
            verifyResult.className = 'verify-result success';
            typeSelect.disabled = false;
            amountInput.disabled = false;
            purchaseBtn.disabled = false;
        } else {
            verifyResult.textContent = 'Error: ' + result.message;
            verifyResult.className = 'verify-result error';
        }
    } catch (error) {
        verifyResult.textContent = 'An unexpected error occurred.';
        verifyResult.className = 'verify-result error';
    } finally {
        this.textContent = 'Verify';
        this.disabled = false;
        verifyResult.style.display = 'block';
    }
});
</script>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>