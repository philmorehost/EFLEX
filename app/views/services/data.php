<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/data" method="post" class="service-form">
        <div class="form-group">
            <label for="network">Select Network</label>
            <select name="network" id="network" class="form-control" required>
                <option value="">-- Select Network --</option>
                <option value="mtn">MTN</option>
                <option value="glo">GLO</option>
                <option value="airtel">Airtel</option>
                <option value="9mobile">9mobile</option>
            </select>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone_number" id="phone_number" class="form-control" placeholder="Enter phone number" required>
        </div>

        <div class="form-group">
            <label for="data_type">Data Type</label>
            <select name="type" id="data_type" class="form-control" required>
                <option value="">-- Select network first --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="data_plan">Data Plan</label>
            <select name="quantity" id="data_plan" class="form-control" required>
                <option value="">-- Select data type first --</option>
            </select>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="text" id="data_price" class="form-control" value="₦0.00" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Purchase Data</button>
    </form>
</div>

<style>
    /* Styles are inherited from airtime view, but adding some specifics if needed */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .service-form { margin-top: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-control { width: 100%; padding: .75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1rem; }
    .btn { display: inline-block; font-weight: 400; padding: .75rem 1.5rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; width: 100%; }
    .btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    #data_price { background-color: #e9ecef; font-weight: bold; color: #28a745; }
</style>

<script>
// Data plans based on the provided API documentation
const allDataPlans = {
    mtn: {
        'sme-data': [
            { quantity: '1gb', price: 550, text: '1GB SME - ₦550' },
            { quantity: '2gb', price: 1100, text: '2GB SME - ₦1100' },
            { quantity: '3gb', price: 1650, text: '3GB SME - ₦1650' }
        ],
        'cg-data': [
            { quantity: '1gb', price: 630, text: '1GB CG - ₦630' },
            { quantity: '2gb', price: 1260, text: '2GB CG - ₦1260' }
        ]
    },
    glo: {
        'cg-data': [
            { quantity: '1gb', price: 420, text: '1GB CG - ₦420' },
            { quantity: '2gb', price: 840, text: '2GB CG - ₦840' }
        ]
    },
    airtel: {
        'cg-data': [
            { quantity: '1gb', price: 810, text: '1GB CG - ₦810' },
            { quantity: '2gb', price: 1510, text: '2GB CG - ₦1510' }
        ]
    },
    '9mobile': {
        'cg-data': [
            { quantity: '1gb', price: 370, text: '1GB CG - ₦370' },
            { quantity: '2gb', price: 740, text: '2GB CG - ₦740' }
        ]
    }
};

const networkSelect = document.getElementById('network');
const typeSelect = document.getElementById('data_type');
const planSelect = document.getElementById('data_plan');
const priceInput = document.getElementById('data_price');

networkSelect.addEventListener('change', function() {
    const selectedNetwork = this.value;
    // Clear subsequent dropdowns
    typeSelect.innerHTML = '<option value="">-- Select data type --</option>';
    planSelect.innerHTML = '<option value="">-- Select data plan --</option>';
    priceInput.value = '₦0.00';

    if (selectedNetwork && allDataPlans[selectedNetwork]) {
        const networkTypes = Object.keys(allDataPlans[selectedNetwork]);
        networkTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type.replace('-', ' ').toUpperCase();
            typeSelect.appendChild(option);
        });
    }
});

typeSelect.addEventListener('change', function() {
    const selectedNetwork = networkSelect.value;
    const selectedType = this.value;
    planSelect.innerHTML = '<option value="">-- Select data plan --</option>';
    priceInput.value = '₦0.00';

    if (selectedNetwork && selectedType && allDataPlans[selectedNetwork][selectedType]) {
        const plans = allDataPlans[selectedNetwork][selectedType];
        plans.forEach(plan => {
            const option = document.createElement('option');
            option.value = plan.quantity;
            option.textContent = plan.text;
            option.dataset.price = plan.price; // Store price in data attribute
            planSelect.appendChild(option);
        });
    }
});

planSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.dataset.price || 0;
    priceInput.value = '₦' + parseFloat(price).toFixed(2);
});

</script>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>