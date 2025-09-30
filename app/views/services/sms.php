<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>
    <p><strong>Price:</strong> ₦<?php echo number_format($data['price_per_sms'], 2); ?> per SMS recipient.</p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/sms" method="post" class="service-form">
        <div class="form-group">
            <label for="sender_id">Sender ID (3-11 characters)</label>
            <input type="text" name="sender_id" id="sender_id" class="form-control" required minlength="3" maxlength="11">
        </div>

        <div class="form-group">
            <label for="phone_number">Recipient Phone Numbers</label>
            <textarea name="phone_number" id="phone_number" class="form-control" rows="5" placeholder="Enter numbers separated by commas, e.g., 080..., 090..."></textarea>
            <small>Total Recipients: <span id="recipient_count">0</span></small>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea name="message" id="message" class="form-control" rows="5" placeholder="Your message here..." required></textarea>
            <small>Characters: <span id="char_count">0</span> | Pages: <span id="page_count">1</span></small>
        </div>

        <div class="form-group">
            <label>Total Cost</label>
            <input type="text" id="total_cost" class="form-control" value="₦0.00" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Send SMS</button>
    </form>
</div>

<style>
    /* Styles are inherited */
    .service-container { width: 90%; max-width: 600px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    #total_cost { background-color: #e9ecef; font-weight: bold; color: #28a745; }
    textarea { resize: vertical; }
</style>

<script>
const phoneInput = document.getElementById('phone_number');
const messageInput = document.getElementById('message');
const recipientCountSpan = document.getElementById('recipient_count');
const charCountSpan = document.getElementById('char_count');
const pageCountSpan = document.getElementById('page_count');
const totalCostInput = document.getElementById('total_cost');
const pricePerSms = <?php echo $data['price_per_sms']; ?>;

function calculateCost() {
    const numbersRaw = phoneInput.value.trim();
    const recipients = numbersRaw ? numbersRaw.split(',').filter(n => n.trim() !== '').length : 0;
    recipientCountSpan.textContent = recipients;

    const message = messageInput.value;
    const charCount = message.length;
    charCountSpan.textContent = charCount;

    // Standard SMS page length is 160 characters
    const pageCount = Math.ceil(charCount / 160) || 1;
    pageCountSpan.textContent = pageCount;

    const totalCost = pricePerSms * recipients * pageCount;
    totalCostInput.value = '₦' + totalCost.toFixed(2);
}

phoneInput.addEventListener('input', calculateCost);
messageInput.addEventListener('input', calculateCost);

// Initial calculation
calculateCost();
</script>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>