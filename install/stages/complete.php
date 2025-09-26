<?php
// Destroy the session to clean up installation data.
session_destroy();
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
                <p class="lead">Congratulations! Your script has been installed successfully.</p>

                <div class="alert alert-danger">
                    <strong>IMPORTANT:</strong> For security reasons, please delete the <strong><code>/install</code></strong> directory from your server immediately.
                </div>

                <h2 class="mt-4">What's Next?</h2>
                <p>You can now log in to your admin panel to start managing your site.</p>
                <a href="../index.php" class="btn btn-primary">Go to Homepage</a>
                <a href="../admin/" class="btn btn-secondary">Go to Admin Panel</a>

                <hr>

                <h4>Admin Usage Guide:</h4>
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item"><strong>Admin Panel:</strong> Your central hub for managing users, products, categories, and site settings.</li>
                    <li class="list-group-item"><strong>User Management:</strong> View, edit, suspend, or delete users. You can also log in to any user's account for troubleshooting.</li>
                    <li class="list-group-item"><strong>Product Approval:</strong> All user-submitted products will appear in the admin panel for you to approve or reject.</li>
                    <li class="list-group-item"><strong>Site Customization:</strong> Use the settings page to manage your site's SEO, PWA features, and landing page content.</li>
                </ul>
            </div>
            <div class="card-footer text-muted">
                Thank you for choosing this script!
            </div>
        </div>
    </div>
</body>
</html>