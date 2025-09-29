<?php
// Start session to access stored db credentials and store site/admin info.
session_start();

// Redirect back to step 2 if database credentials are not set.
if (!isset($_SESSION['db_credentials'])) {
    header('Location: index.php?step=2');
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $site_name = trim($_POST['site_name'] ?? '');
    $admin_email = filter_var(trim($_POST['admin_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';

    // Basic validation
    if (empty($site_name) || empty($admin_email) || empty($admin_username) || empty($admin_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($admin_password !== $admin_password_confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($admin_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Store the details in the session
        $_SESSION['site_details'] = [
            'site_name' => $site_name,
            'admin_email' => $admin_email,
            'admin_username' => $admin_username,
            'admin_password' => $admin_password, // In a real app, hash this immediately. For installer, we hash it during the final step.
        ];

        // Redirect to the final step
        header('Location: index.php?step=4');
        exit;
    }
}
?>

<h2>Step 3: Site & Admin Configuration</h2>
<p>Now, let's set up your site's basic information and create the main administrator account.</p>

<?php if ($error): ?>
    <div class="notice error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form id="settings-form" method="POST">
    <h4>Site Details</h4>
    <div class="form-group">
        <label for="site_name">Site Name</label>
        <input type="text" id="site_name" name="site_name" required>
    </div>

    <h4>Administrator Account</h4>
    <div class="form-group">
        <label for="admin_username">Admin Username</label>
        <input type="text" id="admin_username" name="admin_username" required>
    </div>
    <div class="form-group">
        <label for="admin_email">Admin Email</label>
        <input type="email" id="admin_email" name="admin_email" required>
    </div>
    <div class="form-group">
        <label for="admin_password">Admin Password</label>
        <input type="password" id="admin_password" name="admin_password" required>
    </div>
    <div class="form-group">
        <label for="admin_password_confirm">Confirm Password</label>
        <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
    </div>

    <div class="installer-footer">
        <button type="submit" class="btn">Next Step</button>
    </div>
</form>

<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .notice { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    .notice.error { background-color: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
</style>