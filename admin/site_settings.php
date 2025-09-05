<?php
// Include admin header
include 'includes/header.php';

$message = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Handle PWA Icon Uploads
    $pwa_icon_sizes = ['192', '512'];
    foreach($pwa_icon_sizes as $size) {
        $input_name = 'pwa_icon_' . $size;
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $filename = "icon-{$size}x{$size}.png";
            $destination = __DIR__ . '/../' . $filename;
            if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $destination)) {
                $sql_icon = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                $stmt_icon = $mysqli->prepare($sql_icon);
                $key = 'pwa_icon_'.$size.'_url';
                $value = $filename;
                $stmt_icon->bind_param("ss", $key, $value);
                $stmt_icon->execute();
                $stmt_icon->close();
            }
        }
    }

    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    if($stmt = $mysqli->prepare($sql)){
        $key = '';
        $value = '';
        $stmt->bind_param("ss", $key, $value);

        foreach($_POST as $post_key => $post_value){
            if ($post_key === 'save_settings') continue;
            $key = $post_key;
            $value = trim($post_value);
            $stmt->execute();
        }
        $stmt->close();

        // Force settings to be reloaded on next page load
        global $app_settings;
        $app_settings = null;

        $message = '<div class="alert alert-success">Settings saved successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error preparing statement to save settings.</div>';
    }
}

// Note: The get_app_setting() function is now used directly in the HTML,
// which is loaded from includes/helpers.php via the admin header.
?>

<h1>Site Settings</h1>
<p class="lead">Configure general site, payment, and application settings.</p>

<?php echo $message; ?>

<form action="site_settings.php" method="post" enctype="multipart/form-data">
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-info-circle"></i> Site Information</div>
        <div class="card-body">
            <div class="mb-3"><label for="site_title" class="form-label">Site Title</label><input type="text" name="site_title" class="form-control" id="site_title" value="<?php echo get_app_setting('site_title', 'Eflex E-commerce'); ?>"></div>
            <div class="mb-3"><label for="site_info" class="form-label">Site Information (Footer About Us)</label><textarea name="site_info" class="form-control" id="site_info" rows="4"><?php echo get_app_setting('site_info'); ?></textarea></div>
            <div class="mb-3"><label for="footer_copyright_text" class="form-label">Footer Copyright Text</label><input type="text" name="footer_copyright_text" class="form-control" id="footer_copyright_text" value="<?php echo get_app_setting('footer_copyright_text', '&copy; ' . date("Y") . ' ' . get_app_setting('site_title') . '. All Rights Reserved.'); ?>"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-chart-line"></i> SEO & Analytics</div>
        <div class="card-body">
            <div class="mb-3"><label for="seo_meta_description" class="form-label">Meta Description</label><textarea name="seo_meta_description" class="form-control" id="seo_meta_description" rows="2"><?php echo get_app_setting('seo_meta_description'); ?></textarea></div>
            <div class="mb-3"><label for="seo_meta_keywords" class="form-label">Meta Keywords</label><input type="text" name="seo_meta_keywords" class="form-control" id="seo_meta_keywords" value="<?php echo get_app_setting('seo_meta_keywords'); ?>"></div>
            <div class="mb-3"><label for="google_analytics_id" class="form-label">Google Analytics Tracking ID</label><input type="text" name="google_analytics_id" class="form-control" id="google_analytics_id" value="<?php echo get_app_setting('google_analytics_id'); ?>"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-mobile-alt"></i> PWA (Progressive Web App) Settings</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="pwa_name" class="form-label">App Name</label><input type="text" name="pwa_name" class="form-control" id="pwa_name" value="<?php echo get_app_setting('pwa_name', get_app_setting('site_title')); ?>"></div>
                <div class="col-md-6 mb-3"><label for="pwa_short_name" class="form-label">App Short Name</label><input type="text" name="pwa_short_name" class="form-control" id="pwa_short_name" value="<?php echo get_app_setting('pwa_short_name', get_app_setting('site_title')); ?>"></div>
                <div class="col-md-6 mb-3"><label for="pwa_theme_color" class="form-label">Theme Color</label><input type="color" name="pwa_theme_color" class="form-control form-control-color" id="pwa_theme_color" value="<?php echo get_app_setting('pwa_theme_color', '#ffffff'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="pwa_background_color" class="form-label">Background Color</label><input type="color" name="pwa_background_color" class="form-control form-control-color" id="pwa_background_color" value="<?php echo get_app_setting('pwa_background_color', '#ffffff'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="pwa_icon_192" class="form-label">App Icon (192x192)</label><input type="file" name="pwa_icon_192" class="form-control" id="pwa_icon_192"><div class="form-text">Current: <?php echo get_app_setting('pwa_icon_192_url', 'Not set'); ?></div></div>
                <div class="col-md-6 mb-3"><label for="pwa_icon_512" class="form-label">App Icon (512x512)</label><input type="file" name="pwa_icon_512" class="form-control" id="pwa_icon_512"><div class="form-text">Current: <?php echo get_app_setting('pwa_icon_512_url', 'Not set'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-address-book"></i> Contact & Social Media</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="contact_email" class="form-label">Contact Email</label><input type="email" name="contact_email" class="form-control" id="contact_email" value="<?php echo get_app_setting('contact_email'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="contact_phone" class="form-label">Contact Phone</label><input type="text" name="contact_phone" class="form-control" id="contact_phone" value="<?php echo get_app_setting('contact_phone'); ?>"></div>
                <div class="col-12 mb-3"><label for="contact_address" class="form-label">Contact Address</label><input type="text" name="contact_address" class="form-control" id="contact_address" value="<?php echo get_app_setting('contact_address'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="social_facebook" class="form-label">Facebook URL</label><input type="url" name="social_facebook" class="form-control" id="social_facebook" value="<?php echo get_app_setting('social_facebook'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="social_twitter" class="form-label">Twitter URL</label><input type="url" name="social_twitter" class="form-control" id="social_twitter" value="<?php echo get_app_setting('social_twitter'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="social_instagram" class="form-label">Instagram URL</label><input type="url" name="social_instagram" class="form-control" id="social_instagram" value="<?php echo get_app_setting('social_instagram'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="social_linkedin" class="form-label">LinkedIn URL</label><input type="url" name="social_linkedin" class="form-control" id="social_linkedin" value="<?php echo get_app_setting('social_linkedin'); ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-money-bill-wave"></i> Currency Settings</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="currency_code" class="form-label">Currency Code</label><input type="text" name="currency_code" class="form-control" id="currency_code" value="<?php echo get_app_setting('currency_code', 'USD'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="currency_symbol" class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" id="currency_symbol" value="<?php echo get_app_setting('currency_symbol', '$'); ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-university"></i> Bank Transfer Details</div>
        <div class="card-body">
            <div class="mb-3"><label for="bank_account_name" class="form-label">Account Name</label><input type="text" name="bank_account_name" class="form-control" id="bank_account_name" value="<?php echo get_app_setting('bank_account_name'); ?>"></div>
            <div class="mb-3"><label for="bank_account_number" class="form-label">Account Number</label><input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="<?php echo get_app_setting('bank_account_number'); ?>"></div>
            <div class="mb-3"><label for="bank_name" class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" id="bank_name" value="<?php echo get_app_setting('bank_name'); ?>"></div>
            <div class="mb-3"><label for="bank_payment_instructions" class="form-label">Payment Instructions</label><textarea name="bank_payment_instructions" class="form-control" id="bank_payment_instructions" rows="4"><?php echo get_app_setting('bank_payment_instructions', 'Please make a transfer to the account details above. Use your Order ID as the payment reference.'); ?></textarea></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-envelope"></i> SMTP Email Settings</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="smtp_host" class="form-label">SMTP Host</label><input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?php echo get_app_setting('smtp_host'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="smtp_port" class="form-label">SMTP Port</label><input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?php echo get_app_setting('smtp_port'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="smtp_user" class="form-label">SMTP Username</label><input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?php echo get_app_setting('smtp_user'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="smtp_pass" class="form-label">SMTP Password</label><input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?php echo get_app_setting('smtp_pass'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="from_email" class="form-label">From Email Address</label><input type="email" name="from_email" class="form-control" id="from_email" value="<?php echo get_app_setting('from_email'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="from_name" class="form-label">From Name</label><input type="text" name="from_name" class="form-control" id="from_name" value="<?php echo get_app_setting('from_name'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="smtp_encryption" class="form-label">Encryption</label><select name="smtp_encryption" id="smtp_encryption" class="form-select"><option value="none" <?php if(get_app_setting('smtp_encryption') == 'none') echo 'selected'; ?>>None</option><option value="tls" <?php if(get_app_setting('smtp_encryption') == 'tls') echo 'selected'; ?>>TLS</option><option value="ssl" <?php if(get_app_setting('smtp_encryption') == 'ssl') echo 'selected'; ?>>SSL</option></select></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-key"></i> Paystack API Keys</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="paystack_secret_key" class="form-label">Paystack Secret Key</label><input type="password" name="paystack_secret_key" class="form-control" id="paystack_secret_key" value="<?php echo get_app_setting('paystack_secret_key'); ?>"></div>
                <div class="col-md-6 mb-3"><label for="paystack_public_key" class="form-label">Paystack Public Key</label><input type="text" name="paystack_public_key" class="form-control" id="paystack_public_key" value="<?php echo get_app_setting('paystack_public_key'); ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-clock"></i> Cron Job Setup</div>
        <div class="card-body">
            <p>To automatically handle expired subscriptions, you need to set up a cron job on your server.</p>
            <pre class="bg-light p-3 rounded"><code>* * * * * /usr/bin/php <?php echo realpath(__DIR__ . '/../cron/expire_subscriptions.php'); ?></code></pre>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
