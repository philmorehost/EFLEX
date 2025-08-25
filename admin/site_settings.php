<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Include database connection file
require_once "../includes/db_connect.php";

$message = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Using INSERT ... ON DUPLICATE KEY UPDATE (UPSERT)
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

    if($stmt = $mysqli->prepare($sql)){
        $settings_to_save = [
            'bank_account_name' => $_POST['bank_account_name'],
            'bank_account_number' => $_POST['bank_account_number'],
            'bank_name' => $_POST['bank_name'],
            'bank_payment_instructions' => $_POST['bank_payment_instructions'],
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_user' => $_POST['smtp_user'],
            'smtp_pass' => $_POST['smtp_pass'],
            'from_email' => $_POST['from_email'],
            'from_name' => $_POST['from_name'],
            'smtp_encryption' => $_POST['smtp_encryption'],
        ];

        foreach($settings_to_save as $key => $value){
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $stmt->close();
        $message = '<div class="alert alert-success">Settings saved successfully.</div>';
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom_style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar -->
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Site Settings</h2>
    </div>

    <?php echo $message; ?>

    <form action="site_settings.php" method="post">
        <div class="card">
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
                    <div class="form-text">These instructions will be shown to the user after they select the Bank Transfer option at checkout.</div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">SMTP Email Settings</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="smtp_port" class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="smtp_user" class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" id="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="smtp_pass" class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" id="smtp_pass" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="from_email" class="form-label">From Email Address</label>
                        <input type="email" name="from_email" class="form-control" id="from_email" value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>">
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="from_name" class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" id="from_name" value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
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
        <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
    </form>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
