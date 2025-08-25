<?php
// Include the new admin header
include 'includes/admin_header.php';
require_permission('manage_site_settings');

$message = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // We are saving key-value pairs, so we can just loop through the POST data
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = $mysqli->prepare($sql);

    foreach($_POST as $key => $value){
        // Skip file inputs and the submit button
        if(strpos($key, '_icon_') !== false || $key === 'save_settings') continue;

        $value = is_array($value) ? json_encode($value) : $value;
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }

    // Handle File Uploads for PWA icons
    $pwa_icons = [];
    if(isset($_FILES['pwa_icons']['name']) && !empty($_FILES['pwa_icons']['name'][0])){
        $pwa_icon_dir = "../uploads/pwa/";
        if(!is_dir($pwa_icon_dir)) mkdir($pwa_icon_dir, 0777, true);

        foreach($_FILES['pwa_icons']['name'] as $key => $name){
            $size = str_replace('pwa_icon_', '', $_FILES['pwa_icons']['name'][$key]); // This is not reliable, better to use the key
            $size_key = array_keys($_FILES['pwa_icons']['name'])[$key];
            $size = str_replace('pwa_icon_', '', $size_key);

            $new_filename = "icon-{$size}.png";
            if(move_uploaded_file($_FILES['pwa_icons']['tmp_name'][$key], $pwa_icon_dir . $new_filename)){
                $pwa_icons[] = ['src' => '/uploads/pwa/' . $new_filename, 'sizes' => "{$size}x{$size}", 'type' => 'image/png'];
            }
        }
        if(!empty($pwa_icons)){
            $key = 'pwa_icons';
            $value = json_encode($pwa_icons);
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }

    $stmt->close();
    $message = '<div class="alert alert-success">Settings saved successfully.</div>';
}


// Fetch current settings to display in the form
$settings_sql = "SELECT setting_key, setting_value FROM settings";
$result = $mysqli->query($settings_sql);
$settings = [];
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Site Settings</h2>
</div>

<?php echo $message; ?>

<form action="site_settings.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <!-- General Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">General Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label for="site_name" class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" id="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Eflex'); ?>"></div>
                    <div class="row">
                        <div class="col-md-6"><label for="currency_code" class="form-label">Currency Code</label><input type="text" name="currency_code" class="form-control" id="currency_code" value="<?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?>"></div>
                        <div class="col-md-6"><label for="currency_symbol" class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" id="currency_symbol" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="language" class="form-label">Site Language</label>
                            <select name="language" id="language" class="form-select">
                                <option value="en" <?php if(($settings['language'] ?? 'en') == 'en') echo 'selected'; ?>>English</option>
                                <option value="es" <?php if(($settings['language'] ?? '') == 'es') echo 'selected'; ?>>Español</option>
                                <option value="fr" <?php if(($settings['language'] ?? '') == 'fr') echo 'selected'; ?>>Français</option>
                            </select>
                        </div>
                    </div>
                     <div class="mt-3"><label for="copyright_text" class="form-label">Copyright Text</label><textarea name="copyright_text" class="form-control" id="copyright_text" rows="3"><?php echo htmlspecialchars($settings['copyright_text'] ?? '© ' . date('Y') . ' Eflex E-commerce. All Rights Reserved.'); ?></textarea></div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">SEO Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label for="meta_title" class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" id="meta_title" value="<?php echo htmlspecialchars($settings['meta_title'] ?? ''); ?>"></div>
                    <div class="mb-3"><label for="meta_description" class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" id="meta_description" rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea></div>
                    <div class="mb-3"><label for="meta_keywords" class="form-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control" id="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>"><div class="form-text">Comma-separated values.</div></div>
                </div>
            </div>

            <!-- Payment Gateway Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Payment Gateway Settings</div>
                <div class="card-body">
                    <!-- Bank Transfer -->
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="bank_transfer_enabled" value="1" <?php echo !empty($settings['bank_transfer_enabled']) ? 'checked' : ''; ?>><label class="form-check-label">Enable Bank Transfer</label></div><hr>
                    <!-- Paystack -->
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="paystack_enabled" value="1" <?php echo !empty($settings['paystack_enabled']) ? 'checked' : ''; ?>><label class="form-check-label">Enable Paystack</label></div>
                    <div class="mb-3"><label class="form-label">Paystack Public Key</label><input type="text" name="paystack_public_key" class="form-control" value="<?php echo htmlspecialchars($settings['paystack_public_key'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Paystack Secret Key</label><input type="password" name="paystack_secret_key" class="form-control" value="<?php echo htmlspecialchars($settings['paystack_secret_key'] ?? ''); ?>"></div><hr>
                    <!-- Stripe -->
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="stripe_enabled" value="1" <?php echo !empty($settings['stripe_enabled']) ? 'checked' : ''; ?>><label class="form-check-label">Enable Stripe</label></div>
                    <div class="mb-3"><label class="form-label">Stripe Publishable Key</label><input type="text" name="stripe_public_key" class="form-control" value="<?php echo htmlspecialchars($settings['stripe_public_key'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Stripe Secret Key</label><input type="password" name="stripe_secret_key" class="form-control" value="<?php echo htmlspecialchars($settings['stripe_secret_key'] ?? ''); ?>"></div><hr>
                    <!-- Flutterwave -->
                    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="flutterwave_enabled" value="1" <?php echo !empty($settings['flutterwave_enabled']) ? 'checked' : ''; ?>><label class="form-check-label">Enable Flutterwave</label></div>
                    <div class="mb-3"><label class="form-label">Flutterwave Public Key</label><input type="text" name="flutterwave_public_key" class="form-control" value="<?php echo htmlspecialchars($settings['flutterwave_public_key'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Flutterwave Secret Key</label><input type="password" name="flutterwave_secret_key" class="form-control" value="<?php echo htmlspecialchars($settings['flutterwave_secret_key'] ?? ''); ?>"></div>
                </div>
            </div>
             <!-- PWA Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Progressive Web App (PWA) Settings</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="pwa_enabled" value="1" <?php echo !empty($settings['pwa_enabled']) ? 'checked' : ''; ?>><label class="form-check-label">Enable PWA for Main Site</label></div>
                    <div class="mb-3"><label class="form-label">App Name</label><input type="text" name="pwa_app_name" class="form-control" value="<?php echo htmlspecialchars($settings['pwa_app_name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">App Short Name</label><input type="text" name="pwa_app_short_name" class="form-control" value="<?php echo htmlspecialchars($settings['pwa_app_short_name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">App Theme Color</label><input type="color" name="pwa_theme_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['pwa_theme_color'] ?? '#ffffff'); ?>"></div>
                    <div class="mb-3"><label class="form-label">App Background Color</label><input type="color" name="pwa_bg_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['pwa_bg_color'] ?? '#000000'); ?>"></div>
                    <div class="mb-3"><label class="form-label">App Icons (.png)</label><input type="file" name="pwa_icons[]" class="form-control" multiple><div class="form-text">Upload all required sizes (e.g., 192x192, 512x512). Name them `icon-192x192.png`, etc.</div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Theme & Color Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Theme & Color Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Primary Color</label><input type="color" name="theme_primary_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['theme_primary_color'] ?? '#ae8e6a'); ?>"></div>
                    <div class="mb-3"><label class="form-label">Secondary Color</label><input type="color" name="theme_secondary_color" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['theme_secondary_color'] ?? '#f2f2f2'); ?>"></div>
                </div>
            </div>
             <!-- Bank Transfer Details -->
            <div class="card shadow mb-4">
                <div class="card-header">Bank Transfer Details</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Account Name</label><input type="text" name="bank_account_name" class="form-control" value="<?php echo htmlspecialchars($settings['bank_account_name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Account Number</label><input type="text" name="bank_account_number" class="form-control" value="<?php echo htmlspecialchars($settings['bank_account_number'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Payment Instructions</label><textarea name="bank_payment_instructions" class="form-control" rows="4"><?php echo htmlspecialchars($settings['bank_payment_instructions'] ?? ''); ?></textarea></div>
                </div>
            </div>
            <!-- SMTP Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">SMTP Email Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">SMTP Port</label><input type="text" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">SMTP Username</label><input type="text" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">SMTP Password</label><input type="password" name="smtp_pass" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">From Email</label><input type="email" name="from_email" class="form-control" value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">From Name</label><input type="text" name="from_name" class="form-control" value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="none" <?php if( ($settings['smtp_encryption'] ?? '') == 'none') echo 'selected'; ?>>None</option>
                            <option value="tls" <?php if( ($settings['smtp_encryption'] ?? '') == 'tls') echo 'selected'; ?>>TLS</option>
                            <option value="ssl" <?php if( ($settings['smtp_encryption'] ?? '') == 'ssl') echo 'selected'; ?>>SSL</option>
                        </select>
                    </div>
                </div>
            </div>
             <!-- Push Notification Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Push Notification (OneSignal)</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">OneSignal App ID</label><input type="text" name="onesignal_app_id" class="form-control" value="<?php echo htmlspecialchars($settings['onesignal_app_id'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">REST API Key</label><input type="password" name="onesignal_rest_api_key" class="form-control" value="<?php echo htmlspecialchars($settings['onesignal_rest_api_key'] ?? ''); ?>"></div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" name="save_settings" class="btn btn-primary mt-3 mb-4">Save All Settings</button>
</form>

<?php
// Include the new admin footer
include 'includes/admin_footer.php';
?>
