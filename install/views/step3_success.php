<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketplace Installer - Success!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Installation Successful!</h1>

        <div class="final-success">
            <p>Congratulations! The marketplace script has been installed successfully.</p>
            <p>You can now log in with the following administrator credentials:</p>
            <ul>
                <li><strong>Email:</strong> <code><?php echo htmlspecialchars($_SESSION['admin_email']); ?></code></li>
                <li><strong>Password:</strong> <code><?php echo htmlspecialchars($_SESSION['admin_password']); ?></code></li>
            </ul>
        </div>

        <div class="warning">
            <strong>IMPORTANT:</strong> For security reasons, you must now delete the entire <strong>/install</strong> directory from your server.
        </div>

        <br>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
