<?php
// Clear session data
session_destroy();

// Optionally, you can try to delete the installer directory for security.
// This might fail due to file permissions.
// A better approach is to instruct the user to delete it.

$installer_dir = __DIR__; // or realpath('../install')

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card text-center">
            <div class="card-header">
                <h1>Installation Successful!</h1>
            </div>
            <div class="card-body">
                <p class="lead">Your script has been installed successfully.</p>

                <div class="alert alert-warning">
                    <strong>Security Warning:</strong> Please delete the <strong><code>/install</code></strong> directory from your server immediately.
                </div>

                <h2>Next Steps</h2>
                <p>Here are some quick links to get you started:</p>
                <a href="../index.php" class="btn btn-primary">Go to Homepage</a>
                <a href="../admin/index.php" class="btn btn-secondary">Go to Admin Panel</a>

                <hr>

                <h4>Admin Usage Guide:</h4>
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item"><strong>Admin Panel:</strong> Manage users, products, categories, and site settings from the Admin Panel.</li>
                    <li class="list-group-item"><strong>User Management:</strong> You can view, edit, suspend, or delete users. You also have the ability to log in to any user's account.</li>
                    <li class="list-group-item"><strong>SEO & Landing Page:</strong> Update your site's SEO metadata and customize sections of the landing page directly from the admin settings.</li>
                    <li class="list-group-item"><strong>PWA (Progressive Web App):</strong> Enable or customize PWA settings to allow users to 'install' your site on their devices.</li>
                </ul>
            </div>
            <div class="card-footer text-muted">
                Thank you for choosing our script!
            </div>
        </div>
    </div>
</body>
</html>