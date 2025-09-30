<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="admin-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('settings_success'); ?>

    <form action="<?php echo BASE_URL; ?>/admin/settings" method="post" class="settings-form">
        <h3>Payment Gateway Settings</h3>
        <div class="form-group">
            <label for="payment_beewave_access_key">Beewave Access Key</label>
            <input type="text" name="payment_beewave_access_key" value="<?php echo htmlspecialchars($data['settings']['payment_beewave_access_key'] ?? ''); ?>">
        </div>
         <div class="form-group">
            <label for="payment_beewave_secret_key">Beewave Secret Key</label>
            <input type="text" name="payment_beewave_secret_key" value="<?php echo htmlspecialchars($data['settings']['payment_beewave_secret_key'] ?? ''); ?>">
        </div>
         <div class="form-group">
            <label for="payment_beewave_encrypt_key">Beewave Encrypt Key</label>
            <input type="text" name="payment_beewave_encrypt_key" value="<?php echo htmlspecialchars($data['settings']['payment_beewave_encrypt_key'] ?? ''); ?>">
        </div>
        <hr>
        <div class="form-group">
            <label for="payment_paystack_public_key">Paystack Public Key</label>
            <input type="text" name="payment_paystack_public_key" value="<?php echo htmlspecialchars($data['settings']['payment_paystack_public_key'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="payment_paystack_secret_key">Paystack Secret Key</label>
            <input type="text" name="payment_paystack_secret_key" value="<?php echo htmlspecialchars($data['settings']['payment_paystack_secret_key'] ?? ''); ?>">
        </div>
        <hr>
        <div class="form-group">
            <label for="payment_flutterwave_public_key">Flutterwave Public Key</label>
            <input type="text" name="payment_flutterwave_public_key" value="<?php echo htmlspecialchars($data['settings']['payment_flutterwave_public_key'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="payment_flutterwave_secret_key">Flutterwave Secret Key</label>
            <input type="text" name="payment_flutterwave_secret_key" value="<?php echo htmlspecialchars($data['settings']['payment_flutterwave_secret_key'] ?? ''); ?>">
        </div>

        <h3>VTU Service Settings</h3>
        <div class="form-group">
            <label for="vtu_datagifting_api_key">DataGifting API Key</label>
            <input type="text" name="vtu_datagifting_api_key" value="<?php echo htmlspecialchars($data['settings']['vtu_datagifting_api_key'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-success">Save Settings</button>
    </form>
</div>

<style>
    .admin-container {
        width: 90%;
        max-width: 800px;
        margin: 20px auto;
        background: #fff;
        padding: 2rem;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .settings-form h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: .5rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .btn { display: inline-block; font-weight: 400; padding: .5rem 1rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
    .btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
</style>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>