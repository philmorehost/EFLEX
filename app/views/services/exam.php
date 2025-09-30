<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/exam" method="post" class="service-form">
        <div class="form-group">
            <label for="type">Select Exam Type</label>
            <select name="type" id="type" class="form-control" required>
                <option value="">-- Select Type --</option>
                <?php foreach ($data['exam_pins'] as $code => $details): ?>
                    <option value="<?php echo $code; ?>" data-price="<?php echo $details['price']; ?>">
                        <?php echo htmlspecialchars($details['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>
        </div>

        <div class="form-group">
            <label>Total Price</label>
            <input type="text" id="total_price" class="form-control" value="₦0.00" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Purchase PIN(s)</button>
    </form>
</div>

<style>
    /* Styles are inherited */
    .service-container { width: 90%; max-width: 500px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    #total_price { background-color: #e9ecef; font-weight: bold; color: #28a745; }
</style>

<script>
const typeSelect = document.getElementById('type');
const quantityInput = document.getElementById('quantity');
const priceInput = document.getElementById('total_price');

function calculatePrice() {
    const selectedOption = typeSelect.options[typeSelect.selectedIndex];
    const pricePerPin = parseFloat(selectedOption.dataset.price) || 0;
    const quantity = parseInt(quantityInput.value) || 0;
    const totalPrice = pricePerPin * quantity;

    priceInput.value = '₦' + totalPrice.toFixed(2);
}

typeSelect.addEventListener('change', calculatePrice);
quantityInput.addEventListener('input', calculatePrice);

// Initial calculation on page load
calculatePrice();
</script>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>