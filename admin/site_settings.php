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
            'bank_payment_instructions' => $_POST['bank_payment_instructions']
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
            <div class="card-header">Paystack Settings (Placeholder)</div>
            <div class="card-body">
                <p>Paystack API keys and settings will go here in a future step.</p>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
    </form>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
