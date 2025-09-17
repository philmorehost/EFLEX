<?php
$pageTitle = "System Settings";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) { // Super Admin only
    header("Location: index.php?error=permissiondenied");
    exit;
}

$errors = [];
$success_message = '';

// Helper function to handle file uploads
function handle_file_upload($file_key, $upload_dir, $current_value = null) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];

        // Basic validation
        $allowed_types = ['image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => "Invalid file type for $file_key. Only PNG is allowed."];
        }

        $full_upload_dir = __DIR__ . '/../' . $upload_dir;
        if (!is_dir($full_upload_dir)) {
            mkdir($full_upload_dir, 0755, true);
        }

        // Use a fixed filename based on the key
        $filename = str_replace('_', '-', $file_key) . '.png';
        $new_filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $new_filepath)) {
            return ['filepath' => $new_filepath];
        } else {
            return ['error' => "Failed to move uploaded file for $file_key."];
        }
    }
    return ['filepath' => $current_value]; // No new file uploaded, keep old value
}


// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings_to_update = $_POST['settings'];

    // Handle PWA Icon Uploads
    $upload_dir = 'assets/img/icons/';
    $icon_keys = ['pwa_icon_192', 'pwa_icon_512'];
    foreach ($icon_keys as $key) {
        $current_value = $settings[$key] ?? null;
        $upload_result = handle_file_upload($key, $upload_dir, $current_value);
        if (isset($upload_result['error'])) {
            $errors[] = $upload_result['error'];
        } else {
            if(!empty($upload_result['filepath'])) {
                 $settings_to_update[$key] = $upload_result['filepath'];
            }
        }
    }


    if(empty($errors)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

            foreach ($settings_to_update as $key => $value) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
            $stmt->close();
            $conn->commit();
            $success_message = "Settings updated successfully!";

            // Refresh settings after update
            $settings_result = $conn->query("SELECT * FROM settings");
            $settings = []; // Clear old settings
            while ($row = $settings_result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Failed to update settings: " . $e->getMessage();
        }
    }
}

// --- Fetch all settings ---
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}


require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">System Settings</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">SMTP Mail Server Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="settings[smtp_host]" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="settings[smtp_port]" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_from_name" class="form-label">From Name</label>
                                        <input type="text" class="form-control" id="smtp_from_name" name="settings[smtp_from_name]" placeholder="e.g., Your Site Name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_from_email" class="form-label">Sender Email Address</label>
                                        <input type="email" class="form-control" id="smtp_from_email" name="settings[smtp_from_email]" placeholder="e.g., no-reply@yoursite.com" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                     <div class="col-md-6 mb-3">
                                        <label for="smtp_user" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp_user" name="settings[smtp_user]" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="smtp_pass" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp_pass" name="settings[smtp_pass]" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="smtp_secure" class="form-label">SMTP Security</label>
                                    <select class="form-select" id="smtp_secure" name="settings[smtp_secure]">
                                        <option value="tls" <?php echo (($settings['smtp_secure'] ?? '') == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo (($settings['smtp_secure'] ?? '') == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                        <option value="" <?php echo (empty($settings['smtp_secure'])) ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">PWA (Progressive Web App) Settings</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">These settings will be used in the <code>manifest.json</code> file for users who add the site to their home screen.</p>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pwa_name" class="form-label">App Name</label>
                                        <input type="text" class="form-control" id="pwa_name" name="settings[pwa_name]" value="<?php echo htmlspecialchars($settings['pwa_name'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pwa_short_name" class="form-label">App Short Name</label>
                                        <input type="text" class="form-control" id="pwa_short_name" name="settings[pwa_short_name]" value="<?php echo htmlspecialchars($settings['pwa_short_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="pwa_description" class="form-label">App Description</label>
                                    <textarea class="form-control" id="pwa_description" name="settings[pwa_description]" rows="2"><?php echo htmlspecialchars($settings['pwa_description'] ?? ''); ?></textarea>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pwa_icon_192" class="form-label">App Icon (192x192, PNG)</label>
                                        <input class="form-control" type="file" id="pwa_icon_192" name="pwa_icon_192" accept=".png">
                                        <?php if(!empty($settings['pwa_icon_192'])): ?>
                                            <div class="mt-2">
                                                <small>Current: <img src="../<?php echo htmlspecialchars($settings['pwa_icon_192']); ?>?v=<?php echo time(); ?>" alt="Icon 192" style="width: 32px; height: 32px;"></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pwa_icon_512" class="form-label">App Icon (512x512, PNG)</label>
                                        <input class="form-control" type="file" id="pwa_icon_512" name="pwa_icon_512" accept=".png">
                                         <?php if(!empty($settings['pwa_icon_512'])): ?>
                                            <div class="mt-2">
                                                <small>Current: <img src="../<?php echo htmlspecialchars($settings['pwa_icon_512']); ?>?v=<?php echo time(); ?>" alt="Icon 512" style="width: 32px; height: 32px;"></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
