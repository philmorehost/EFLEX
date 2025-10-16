<?php
// Stage 4: Installation Complete

$pageTitle = "Stage 4: Installation Successful!";

// Clean up the session to remove installer-specific variables
session_destroy();

?>

<h1>Installation Successful!</h1>
<hr>

<div class="alert alert-success">
    <strong>Congratulations!</strong> The CBT Platform has been successfully installed on your server.
</div>

<div class="alert alert-info">
    <p><strong>You can now log in with the default Super Admin account:</strong></p>
    <ul>
        <li><strong>Email:</strong> <code>superadmin@cbt.com</code></li>
        <li><strong>Password:</strong> <code>password123</code></li>
    </ul>
    <p>Please change this password immediately after your first login.</p>
</div>

<div class="alert alert-danger">
    <p><strong>IMPORTANT SECURITY WARNING:</strong></p>
    <p>For the security of your application, you should now <strong>DELETE</strong> the entire <code>/install</code> directory from your server.</p>
</div>

<div style="margin-top: 20px; text-align: right;">
    <a href="../login.php" class="btn">Go to Login Page &raquo;</a>
</div>
