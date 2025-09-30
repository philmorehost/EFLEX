<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>

    <?php
    // Check for successful PIN generation and display them
    if (isset($_SESSION['card_pins_success'])) {
        echo '<div class="alert alert-success"><strong>Purchase Successful! Your PINs are below:</strong><br>';
        $pins = explode(',', $_SESSION['card_pins_success']);
        echo '<textarea class="form-control" rows="10" readonly>' . implode("\n", $pins) . '</textarea>';
        echo '</div>';
        unset($_SESSION['card_pins_success']);
        unset($_SESSION['card_pins_success_class']); // Clean up flash session
    }
    ?>

    <form action="<?php echo BASE_URL; ?>/services/card" method="post" class="service-form">
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
            <label for="amount">Card Amount</label>
            <select name="amount" id="amount" class="form-control" required>
                <option value="">-- Select Network First --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="qty_number">Quantity</label>
            <input type="number" name="qty_number" id="quantity" class="form-control" value="1" min="1" required>
        </div>

        <div class="form-group">
            <label for="card_name">Business Name on Card (Optional)</label>
            <input type="text" name="card_name" id="card_name" class="form-control" placeholder="e.g., BeeTech Solution">
        </div>

        <div class="form-group">
            <label>Total Cost</label>
            <input type="text" id="total_cost" class="form-control" value="₦0.00" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Generate PINs</button>
    </form>
</div>

<style>
    /* Styles are inherited */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    #total_cost { background-color: #e9ecef; font-weight: bold; color: #28a745; }
</style>

<script>
const cardPrices = <?php echo json_encode($data['card_prices']); ?>;
const networkSelect = document.getElementById('network');
const amountSelect = document.getElementById('amount');
const quantityInput = document.getElementById('quantity');
const totalCostInput = document.getElementById('total_cost');

function updateAmounts() {
    const selectedNetwork = networkSelect.value;
    amountSelect.innerHTML = '<option value="">-- Select Amount --</option>'; // Clear previous options

    if (selectedNetwork && cardPrices[selectedNetwork]) {
        const amounts = Object.keys(cardPrices[selectedNetwork]);
        amounts.forEach(amount => {
            const option = document.createElement('option');
            option.value = amount;
            option.textContent = '₦' + amount;
            amountSelect.appendChild(option);
        });
    }
    calculateTotalCost();
}

function calculateTotalCost() {
    const selectedNetwork = networkSelect.value;
    const selectedAmount = amountSelect.value;
    const quantity = parseInt(quantityInput.value) || 0;

    let pricePerCard = 0;
    if (selectedNetwork && selectedAmount && cardPrices[selectedNetwork] && cardPrices[selectedNetwork][selectedAmount]) {
        pricePerCard = cardPrices[selectedNetwork][selectedAmount];
    }

    const totalCost = pricePerCard * quantity;
    totalCostInput.value = '₦' + totalCost.toFixed(2);
}

networkSelect.addEventListener('change', updateAmounts);
amountSelect.addEventListener('change', calculateTotalCost);
quantityInput.addEventListener('input', calculateTotalCost);

</script>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>