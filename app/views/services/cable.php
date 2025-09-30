<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/cable" method="post" class="service-form" id="cable-form">
        <div class="form-group">
            <label for="type">Select Provider</label>
            <select name="type" id="type" class="form-control" required>
                <option value="">-- Select Provider --</option>
                <option value="dstv">DSTV</option>
                <option value="gotv">GOtv</option>
                <option value="startimes">Startimes</option>
            </select>
        </div>

        <div class="form-group">
            <label for="iuc_number">Smartcard / IUC Number</label>
            <div class="input-group">
                <input type="text" name="iuc_number" id="iuc_number" class="form-control" placeholder="Enter card/IUC number" required>
                <button type="button" id="verify-btn" class="btn btn-secondary">Verify</button>
            </div>
            <div id="verify-result" class="verify-result"></div>
        </div>

        <div class="form-group">
            <label for="package">Select Package</label>
            <select name="package" id="package" class="form-control" required disabled>
                <option value="">-- Verify IUC first --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="text" id="cable_price" class="form-control" value="₦0.00" readonly>
        </div>

        <button type="submit" id="purchase-btn" class="btn btn-primary" disabled>Renew Subscription</button>
    </form>
</div>

<style>
    /* Most styles inherited from previous service pages */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-control { width: 100%; padding: .75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1rem; }
    .input-group { display: flex; }
    .input-group .form-control { border-top-right-radius: 0; border-bottom-right-radius: 0; }
    .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .btn { display: inline-block; font-weight: 400; padding: .75rem 1.5rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
    .btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; width: 100%; }
    .btn-secondary { color: #fff; background-color: #6c757d; border-color: #6c757d; }
    .btn:disabled { cursor: not-allowed; opacity: 0.65; }
    .verify-result { margin-top: 10px; padding: 10px; border-radius: 4px; display: none; }
    .verify-result.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .verify-result.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<script>
const cablePackages = {
    dstv: [
        { code: 'padi', text: 'DStv Padi - ₦2150', price: 2150 },
        { code: 'yanga', text: 'DStv Yanga - ₦2950', price: 2950 },
        { code: 'confam', text: 'DStv Confam - ₦5300', price: 5300 },
        { code: 'compact', text: 'DStv Compact - ₦9000', price: 9000 }
    ],
    gotv: [
        { code: 'jolli', text: 'GOtv Jolli - ₦2800', price: 2800 },
        { code: 'max', text: 'GOtv Max - ₦4150', price: 4150 }
    ],
    startimes: [
        { code: 'nova', text: 'Startimes Nova - ₦900', price: 900 },
        { code: 'basic', text: 'Startimes Basic - ₦1700', price: 1700 }
    ]
};

const typeSelect = document.getElementById('type');
const packageSelect = document.getElementById('package');
const priceInput = document.getElementById('cable_price');
const verifyBtn = document.getElementById('verify-btn');
const verifyResult = document.getElementById('verify-result');
const iucInput = document.getElementById('iuc_number');
const purchaseBtn = document.getElementById('purchase-btn');

typeSelect.addEventListener('change', function() {
    const selectedProvider = this.value;
    packageSelect.innerHTML = '<option value="">-- Select package --</option>';
    priceInput.value = '₦0.00';
    purchaseBtn.disabled = true;
    verifyResult.style.display = 'none';

    if (selectedProvider && cablePackages[selectedProvider]) {
        const packages = cablePackages[selectedProvider];
        packages.forEach(pkg => {
            const option = document.createElement('option');
            option.value = pkg.code;
            option.textContent = pkg.text;
            option.dataset.price = pkg.price;
            packageSelect.appendChild(option);
        });
    }
});

packageSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.dataset.price || 0;
    priceInput.value = '₦' + parseFloat(price).toFixed(2);
});

verifyBtn.addEventListener('click', async function() {
    const provider = typeSelect.value;
    const iucNumber = iucInput.value;

    if (!provider || !iucNumber) {
        alert('Please select a provider and enter an IUC number.');
        return;
    }

    this.textContent = 'Verifying...';
    this.disabled = true;
    verifyResult.style.display = 'none';
    purchaseBtn.disabled = true;

    const formData = new FormData();
    formData.append('type', provider);
    formData.append('iuc_number', iucNumber);

    try {
        const response = await fetch('<?php echo BASE_URL; ?>/services/verifyCable', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            verifyResult.textContent = 'Customer: ' + result.customer_name;
            verifyResult.className = 'verify-result success';
            packageSelect.disabled = false;
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