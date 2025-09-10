<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_save = [
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
    ];

    try {
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value";
        $stmt = $pdo->prepare($sql);

        foreach ($settings_to_save as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }
        $_SESSION['success'] = 'Settings saved successfully!';
    } catch (PDOException $e) {
        $_SESSION['errors'] = ['Database error: ' . $e->getMessage()];
    }

    header("Location: settings.php");
    exit();
}

// --- Fetch existing settings ---
try {
    $settings_raw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$settings = array_merge([
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_user' => '',
    'smtp_pass' => '',
    'smtp_encryption' => 'tls',
], $settings_raw);

// Construct the cron job command
$cron_command = "*/5 * * * * /usr/bin/php " . ROOT_PATH . "/cron/run_tasks.php";
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">System Settings</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form action="settings.php" method="POST">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">SMTP Configuration</h6></div>
            <div class="card-body">
                <p>These settings are for sending system emails (e.g., notifications, password resets).</p>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="smtp_host" class="form-label">SMTP Host</label><input type="text" class="form-control" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"></div>
                    <div class="col-md-6 mb-3"><label for="smtp_port" class="form-label">SMTP Port</label><input type="number" class="form-control" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label for="smtp_user" class="form-label">SMTP Username</label><input type="text" class="form-control" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user']); ?>"></div>
                    <div class="col-md-6 mb-3"><label for="smtp_pass" class="form-label">SMTP Password</label><input type="password" class="form-control" name="smtp_pass" value="<?php echo htmlspecialchars($settings['smtp_pass']); ?>"></div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select class="form-select" name="smtp_encryption">
                            <option value="tls" <?php echo ($settings['smtp_encryption'] == 'tls') ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['smtp_encryption'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                            <option value="none" <?php echo ($settings['smtp_encryption'] == 'none') ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Cron Job Information</h6></div>
            <div class="card-body">
                <p>To enable automated tasks like processing scheduled exams, add the following line to your server's crontab:</p>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($cron_command); ?>" readonly>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
