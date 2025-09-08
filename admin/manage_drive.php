<?php
include 'includes/header.php';
require_once '../includes/google_drive_api.php';

$message = "";
$credentials_file = __DIR__ . '/secure/gdrive.dat';

// Handle saving Client ID and Secret
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_credentials'])) {
    $client_id = trim($_POST['client_id']);
    $client_secret = trim($_POST['client_secret']);

    if (!empty($client_id) && !empty($client_secret)) {
        // Read existing credentials to preserve refresh token if it exists
        $existing_creds = file_exists($credentials_file) ? json_decode(file_get_contents($credentials_file), true) : [];
        $new_creds = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $existing_creds['refresh_token'] ?? null
        ];
        if (file_put_contents($credentials_file, json_encode($new_creds, JSON_PRETTY_PRINT))) {
            $message = '<div class="alert alert-success">Credentials saved successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to save credentials. Please check file permissions for <code>/admin/secure/gdrive.dat</code>.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Client ID and Client Secret cannot be empty.</div>';
    }
}

// Get current credentials for display and status check
$credentials = get_google_drive_credentials();
$is_configured = !isset($credentials['error']) && !empty($credentials['client_id']) && !empty($credentials['client_secret']);
$is_connected = $is_configured && !empty($credentials['refresh_token']);

?>

<h1>Google Drive Integration</h1>
<p class="lead">Configure the connection to your Google Drive account.</p>

<?php echo $message; ?>

<div class="card">
    <div class="card-header"><i class="fas fa-cog"></i> Connection Status</div>
    <div class="card-body">
        <?php if ($is_connected): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Connected!</strong> Your site is successfully connected to Google Drive.
            </div>
            <p>You can now add files from your Google Drive when creating or editing a class.</p>
            <a href="gdrive_oauth_start.php" class="btn btn-danger"><i class="fas fa-redo"></i> Re-authorize with Google</a>
            <p class="form-text mt-2">If you are experiencing issues, you can try re-authorizing.</p>
        <?php elseif ($is_configured): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Ready to Connect.</strong> Your credentials are saved. The final step is to authorize access to your Google account.
            </div>
            <p>Click the button below to be redirected to Google to grant permission to your site.</p>
            <a href="gdrive_oauth_start.php" class="btn btn-primary"><i class="fab fa-google-drive"></i> Connect to Google Drive</a>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> <strong>Not Configured.</strong> Please enter your Google API credentials below to begin.
            </div>
            <p>You need to provide a Client ID and Client Secret from your Google Cloud Console project. See the documentation for instructions on how to obtain these.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><i class="fas fa-key"></i> API Credentials</div>
    <div class="card-body">
        <form action="manage_drive.php" method="post">
            <div class="mb-3">
                <label for="client_id" class="form-label">Client ID</label>
                <input type="text" name="client_id" id="client_id" class="form-control" value="<?php echo htmlspecialchars($credentials['client_id'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="client_secret" class="form-label">Client Secret</label>
                <input type="password" name="client_secret" id="client_secret" class="form-control" value="<?php echo htmlspecialchars($credentials['client_secret'] ?? ''); ?>">
            </div>
            <button type="submit" name="save_credentials" class="btn btn-primary">Save Credentials</button>
        </form>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
