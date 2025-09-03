<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    if($stmt = $mysqli->prepare($sql)){
        $key = '';
        $value = '';
        $stmt->bind_param("ss", $key, $value);

        // Loop through all POST data and save it
        foreach($_POST as $post_key => $post_value){
            // Do not save the button's value
            if ($post_key === 'save_settings') continue;
            $key = $post_key;
            $value = trim($post_value);
            $stmt->execute();
        }
        $stmt->close();
        $message = '<div class="alert alert-success">Settings saved successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error preparing statement to save settings.</div>';
    }
}

// Fetch all current settings to display in the form
$settings_sql = "SELECT setting_key, setting_value FROM settings";
$result = $mysqli->query($settings_sql);
$settings = [];
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Helper function to get a setting value or a default
function get_setting($key, $default = '') {
    global $settings;
    return htmlspecialchars($settings[$key] ?? $default);
}
?>

<h1>Site Settings</h1>
<p class="lead">Configure payment and email settings for your store.</p>

<?php echo $message; ?>

<form action="site_settings.php" method="post">
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-university"></i> Bank Transfer Details</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="bank_account_name" class="form-label">Account Name</label>
                <input type="text" name="bank_account_name" class="form-control" id="bank_account_name" value="<?php echo get_setting('bank_account_name'); ?>">
            </div>
            <div class="mb-3">
                <label for="bank_account_number" class="form-label">Account Number</label>
                <input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="<?php echo get_setting('bank_account_number'); ?>">
            </div>
            <div class="mb-3">
                <label for="bank_name" class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" id="bank_name" value="<?php echo get_setting('bank_name'); ?>">
            </div>
             <div class="mb-3">
                <label for="bank_payment_instructions" class="form-label">Payment Instructions</label>
                <textarea name="bank_payment_instructions" class="form-control" id="bank_payment_instructions" rows="4"><?php echo get_setting('bank_payment_instructions', 'Please make a transfer to the account details above. Use your Order ID as the payment reference.'); ?></textarea>
                <div class="form-text">These instructions will be shown to the user after they select the Bank Transfer option at checkout.</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-envelope"></i> SMTP Email Settings</div>
        <div class="card-body">
             <div class="form-text mb-3">Configure your SMTP settings to send order confirmation and other transactional emails.</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="smtp_host" class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?php echo get_setting('smtp_host'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_port" class="form-label">SMTP Port</label>
                    <input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?php echo get_setting('smtp_port'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_user" class="form-label">SMTP Username</label>
                    <input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?php echo get_setting('smtp_user'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_pass" class="form-label">SMTP Password</label>
                    <input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?php echo get_setting('smtp_pass'); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="from_email" class="form-label">From Email Address</label>
                    <input type="email" name="from_email" class="form-control" id="from_email" value="<?php echo get_setting('from_email'); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="from_name" class="form-label">From Name</label>
                    <input type="text" name="from_name" class="form-control" id="from_name" value="<?php echo get_setting('from_name'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_encryption" class="form-label">Encryption</label>
                    <select name="smtp_encryption" id="smtp_encryption" class="form-select">
                        <option value="none" <?php if(get_setting('smtp_encryption') == 'none') echo 'selected'; ?>>None</option>
                        <option value="tls" <?php if(get_setting('smtp_encryption') == 'tls') echo 'selected'; ?>>TLS</option>
                        <option value="ssl" <?php if(get_setting('smtp_encryption') == 'ssl') echo 'selected'; ?>>SSL</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-key"></i> Paystack API Keys</div>
        <div class="card-body">
             <div class="form-text mb-3">Enter your Paystack API keys to enable the payment gateway. You can find these in your Paystack Dashboard under Settings > API Keys & Webhooks.</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="paystack_secret_key" class="form-label">Paystack Secret Key</label>
                    <input type="password" name="paystack_secret_key" class="form-control" id="paystack_secret_key" value="<?php echo get_setting('paystack_secret_key'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="paystack_public_key" class="form-label">Paystack Public Key</label>
                    <input type="text" name="paystack_public_key" class="form-control" id="paystack_public_key" value="<?php echo get_setting('paystack_public_key'); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
    </div>
</form>

<?php
// Include admin footer
include 'includes/footer.php';
?>
