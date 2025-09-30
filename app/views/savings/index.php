<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('savings_error'); ?>
    <?php flash('savings_success'); ?>

    <div class="balance-display">
        <div class="balance-card">
            <h4>Main Wallet</h4>
            <p>₦<?php echo number_format($data['wallet_balance'], 2); ?></p>
        </div>
        <div class="balance-card savings">
            <h4>Savings Vault</h4>
            <p>₦<?php echo number_format($data['savings_balance'], 2); ?></p>
        </div>
    </div>

    <form action="<?php echo BASE_URL; ?>/savings" method="post" class="service-form">
        <div class="form-group">
            <label for="amount">Amount to Save</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="e.g., 1000" required>
        </div>

        <button type="submit" class="btn btn-primary">Transfer to Savings</button>
    </form>
</div>

<style>
    /* Most styles inherited */
    .service-container { width: 90%; max-width: 600px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .balance-display {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .balance-card {
        background: #f7f7f7;
        padding: 1rem;
        border-radius: 5px;
        text-align: center;
    }
    .balance-card h4 {
        margin: 0 0 0.5rem 0;
        color: #666;
    }
    .balance-card p {
        margin: 0;
        font-size: 1.5em;
        font-weight: bold;
    }
    .balance-card.savings p {
        color: #28a745;
    }
</style>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>