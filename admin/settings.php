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

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings_to_update = $_POST['settings'];

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
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = "Failed to update settings: " . $e->getMessage();
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

                    <form action="settings.php" method="POST">
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
