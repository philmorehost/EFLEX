<?php
// Core dependencies must be included first.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

echo "<h1>Admin Verification & Fix Script</h1>";

$admin_user_id = 1;

// --- Step 1: Grant Admin Privileges ---
echo "<strong>Step 1: Attempting to grant admin privileges to user ID #{$admin_user_id}...</strong><br>";
$stmt_update = $mysqli->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
$stmt_update->bind_param('i', $admin_user_id);

if ($stmt_update->execute()) {
    echo "<span style='color:green;'>SUCCESS: Update command executed without errors.</span><br>";
} else {
    echo "<span style='color:red;'>FAILURE: Could not execute update command. Database error: " . htmlspecialchars($stmt_update->error) . "</span><br>";
    exit; // Stop if this fails.
}
$stmt_update->close();

// --- Step 2: Verify the Change ---
echo "<br><strong>Step 2: Fetching user data from the database to verify the change...</strong><br>";
$stmt_select = $mysqli->prepare("SELECT id, username, email, is_admin, suspended FROM users WHERE id = ?");
$stmt_select->bind_param('i', $admin_user_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$user_data = $result->fetch_assoc();
$stmt_select->close();

if ($user_data) {
    echo "User data found:<br>";
    echo "<pre>";
    print_r($user_data);
    echo "</pre>";

    if (isset($user_data['is_admin']) && $user_data['is_admin'] == 1) {
        echo "<strong style='color:green;'>VERIFICATION SUCCESS: The user is now an administrator in the database.</strong><br>";
        echo "<p>Please try logging in to the admin panel now. This issue should be resolved.</p>";
        echo "<p><a href='login.php'>Proceed to Admin Login</a></p>";
    } else {
        echo "<strong style='color:red;'>VERIFICATION FAILED: The 'is_admin' flag is still not set to 1. There might be a database permissions issue.</strong><br>";
    }
} else {
    echo "<strong style='color:red;'>VERIFICATION FAILED: Could not find a user with ID #{$admin_user_id}.</strong><br>";
}

// --- Step 3: Self-Destruct ---
echo "<br><strong>Step 3: Self-destructing for security...</strong><br>";
if (unlink(__FILE__)) {
    echo "<span style='color:blue;'>SUCCESS: This script has been automatically deleted.</span><br>";
} else {
    echo "<span style='color:red; font-weight:bold;'>WARNING: Could not self-delete. Please manually delete the `verify_and_fix_admin.php` file from your server.</span><br>";
}

$mysqli->close();
?>
