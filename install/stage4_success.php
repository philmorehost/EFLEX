<?php
// Stage 4: Installation Complete

$pageTitle = "Stage 4: Installation Successful!";

$admin_email = $_SESSION['admin_details']['email'] ?? '[not set]';
$admin_pass = $_SESSION['admin_details']['pass'] ?? '[not set]';


// Clean up the session to remove installer-specific variables
session_destroy();

?>

<h1>Installation Successful!</h1>
<hr>

<div class="alert alert-success">
    <strong>Congratulations!</strong> The CBT Platform has been successfully installed on your server.
</div>

<div class="alert alert-info">
    <p><strong>You can now log in with the Super Admin account you just created:</strong></p>
    <ul>
        <li><strong>Email:</strong> <code><?php echo htmlspecialchars($admin_email); ?></code></li>
        <li><strong>Password:</strong> <code><?php echo htmlspecialchars($admin_pass); ?></code></li>
    </ul>
    <p>Please keep these details safe. You will need them to log in.</p>
</div>

<div class="alert alert-danger">
    <p><strong>IMPORTANT SECURITY WARNING:</strong></p>
    <p>For the security of your application, you must now <strong>DELETE</strong> the entire <code>/install</code> directory from your server. Leaving it on the server is a major security risk.</p>
</div>

<div style="margin-top: 20px; text-align: right;">
    <a href="../login.php" class="btn">Go to Login Page &raquo;</a>
</div>
