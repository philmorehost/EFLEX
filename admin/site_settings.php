<?php
// Include the new admin header
include 'includes/admin_header.php';
require_permission('manage_site_settings');

$message = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $settings_to_save = [
        'site_name' => $_POST['site_name'] ?? '',
        'paystack_public_key' => $_POST['paystack_public_key'] ?? '',
        'paystack_secret_key' => $_POST['paystack_secret_key'] ?? '',
        'paystack_enabled' => isset($_POST['paystack_enabled']) ? '1' : '0',
        'bank_transfer_enabled' => isset($_POST['bank_transfer_enabled']) ? '1' : '0',
        'bank_account_name' => $_POST['bank_account_name'] ?? '',
        'bank_account_number' => $_POST['bank_account_number'] ?? '',
        'bank_name' => $_POST['bank_name'] ?? '',
        'bank_payment_instructions' => $_POST['bank_payment_instructions'] ?? '',
        'hero_section_title' => $_POST['hero_section_title'] ?? '',
        'hero_section_description' => $_POST['hero_section_description'] ?? '',
        'hero_section_background_url' => $_POST['hero_section_background_url'] ?? '',
        'how_it_works_bg_color' => $_POST['how_it_works_bg_color'] ?? '#f8f9fa',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'from_email' => $_POST['from_email'] ?? '',
        'from_name' => $_POST['from_name'] ?? '',
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'none',
        'onesignal_app_id' => $_POST['onesignal_app_id'] ?? '',
        'onesignal_rest_api_key' => $_POST['onesignal_rest_api_key'] ?? '',
    ];

    // Handle Site Logo Upload
    if(isset($_FILES["site_logo"]) && $_FILES["site_logo"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
        $filename = $_FILES["site_logo"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(in_array($_FILES["site_logo"]["type"], $allowed)){
            $new_filename = 'logo.' . $ext;
            if(move_uploaded_file($_FILES["site_logo"]["tmp_name"], "../uploads/" . $new_filename)){
                $settings_to_save['site_logo'] = $new_filename;
            } else {
                 $message .= '<div class="alert alert-danger">Error uploading site logo.</div>';
            }
        } else {
            $message .= '<div class="alert alert-danger">Invalid file type for site logo.</div>';
        }
    }

    // Handle Hero Background Image Upload
    if(isset($_FILES["hero_section_background_image"]) && $_FILES["hero_section_background_image"]["error"] == 0){
        $allowed = ["jpg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["hero_section_background_image"]["name"];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(in_array($_FILES["hero_section_background_image"]["type"], $allowed)){
            $new_filename = 'hero_bg.' . $ext;
             if(move_uploaded_file($_FILES["hero_section_background_image"]["tmp_name"], "../uploads/" . $new_filename)){
                // We save the path to the uploaded image, overriding any URL
                $settings_to_save['hero_section_background_url'] = 'uploads/' . $new_filename;
            } else {
                 $message .= '<div class="alert alert-danger">Error uploading hero background image.</div>';
            }
        } else {
             $message .= '<div class="alert alert-danger">Invalid file type for hero background.</div>';
        }
    }


    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    if($stmt = $mysqli->prepare($sql)){
        foreach($settings_to_save as $key => $value){
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $stmt->close();
        if(empty($message)) {
            $message = '<div class="alert alert-success">Settings saved successfully.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Error preparing statement.</div>';
    }
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
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Eflex'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="site_logo" class="form-label">Site Logo</label>
                        <input type="file" name="site_logo" class="form-control" id="site_logo">
                        <?php if(isset($settings['site_logo'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Site Logo" style="max-height: 50px;">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Homepage Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Homepage Settings</div>
                <div class="card-body">
                     <div class="mb-3">
                        <label for="hero_section_title" class="form-label">Hero Section Title</label>
                        <input type="text" name="hero_section_title" class="form-control" id="hero_section_title" value="<?php echo htmlspecialchars($settings['hero_section_title'] ?? 'Welcome to Eflex'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="hero_section_description" class="form-label">Hero Section Description</label>
                        <textarea name="hero_section_description" class="form-control" id="hero_section_description" rows="3"><?php echo htmlspecialchars($settings['hero_section_description'] ?? 'Your one-stop shop for everything you need.'); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="hero_section_background_image" class="form-label">Hero Background Image</label>
                        <input type="file" name="hero_section_background_image" class="form-control" id="hero_section_background_image">
                         <div class="form-text">Upload an image to replace the current background.</div>
                    </div>
                     <div class="mb-3">
                        <label for="hero_section_background_url" class="form-label">Hero Background Video URL (e.g., YouTube)</label>
                        <input type="text" name="hero_section_background_url" class="form-control" id="hero_section_background_url" value="<?php echo htmlspecialchars($settings['hero_section_background_url'] ?? ''); ?>">
                        <div class="form-text">Or, provide a URL to a video. If an image is uploaded, it will take precedence.</div>
                    </div>
                    <div class="mb-3">
                        <label for="how_it_works_bg_color" class="form-label">"How It Works" Background Color</label>
                        <input type="color" name="how_it_works_bg_color" class="form-control form-control-color" id="how_it_works_bg_color" value="<?php echo htmlspecialchars($settings['how_it_works_bg_color'] ?? '#f8f9fa'); ?>">
                    </div>
                </div>
            </div>

            <!-- Payment Gateway Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">Payment Gateway Settings</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="paystack_enabled" id="paystack_enabled" value="1" <?php echo (isset($settings['paystack_enabled']) && $settings['paystack_enabled'] == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="paystack_enabled">Enable Paystack</label>
                    </div>
                    <div class="mb-3">
                        <label for="paystack_public_key" class="form-label">Paystack Public Key</label>
                        <input type="text" name="paystack_public_key" class="form-control" id="paystack_public_key" value="<?php echo htmlspecialchars($settings['paystack_public_key'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="paystack_secret_key" class="form-label">Paystack Secret Key</label>
                        <input type="password" name="paystack_secret_key" class="form-control" id="paystack_secret_key" value="<?php echo htmlspecialchars($settings['paystack_secret_key'] ?? ''); ?>">
                    </div>
                    <hr>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="bank_transfer_enabled" id="bank_transfer_enabled" value="1" <?php echo (isset($settings['bank_transfer_enabled']) && $settings['bank_transfer_enabled'] == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="bank_transfer_enabled">Enable Bank Transfer</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
             <!-- Bank Transfer Details -->
            <div class="card shadow mb-4">
                <div class="card-header">Bank Transfer Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="bank_account_name" class="form-label">Account Name</label>
                        <input type="text" name="bank_account_name" class="form-control" id="bank_account_name" value="<?php echo htmlspecialchars($settings['bank_account_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bank_account_number" class="form-label">Account Number</label>
                        <input type="text" name="bank_account_number" class="form-control" id="bank_account_number" value="<?php echo htmlspecialchars($settings['bank_account_number'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" id="bank_name" value="<?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?>">
                    </div>
                     <div class="mb-3">
                        <label for="bank_payment_instructions" class="form-label">Payment Instructions</label>
                        <textarea name="bank_payment_instructions" class="form-control" id="bank_payment_instructions" rows="4"><?php echo htmlspecialchars($settings['bank_payment_instructions'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SMTP Settings -->
            <div class="card shadow mb-4">
                <div class="card-header">SMTP Email Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_port" class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_user" class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_pass" class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                    </div>
                     <div class="mb-3">
                        <label for="from_email" class="form-label">From Email Address</label>
                        <input type="email" name="from_email" class="form-control" id="from_email" value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>">
                    </div>
                     <div class="mb-3">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" id="from_name" value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select name="smtp_encryption" id="smtp_encryption" class="form-select">
                            <option value="none" <?php if( ($settings['smtp_encryption'] ?? '') == 'none') echo 'selected'; ?>>None</option>
                            <option value="tls" <?php if( ($settings['smtp_encryption'] ?? '') == 'tls') echo 'selected'; ?>>TLS</option>
                            <option value="ssl" <?php if( ($settings['smtp_encryption'] ?? '') == 'ssl') echo 'selected'; ?>>SSL</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header">Push Notification Settings (OneSignal)</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="onesignal_app_id" class="form-label">OneSignal App ID</label>
                <input type="text" name="onesignal_app_id" class="form-control" id="onesignal_app_id" value="<?php echo htmlspecialchars($settings['onesignal_app_id'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="onesignal_rest_api_key" class="form-label">OneSignal REST API Key</label>
                <input type="password" name="onesignal_rest_api_key" class="form-control" id="onesignal_rest_api_key" value="<?php echo htmlspecialchars($settings['onesignal_rest_api_key'] ?? ''); ?>">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-3 mb-4">Save Settings</button>
</form>

<?php
// Include the new admin footer
include 'includes/admin_footer.php';
?>
