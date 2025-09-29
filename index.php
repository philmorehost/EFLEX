<?php
/**
 * index.php - Main entry point for the application
 */

// Check if the installer exists and the application is not yet installed.
if (file_exists('installer/index.php') && !file_exists('install.lock')) {
    // Redirect to the installer.
    header('Location: installer/index.php');
    exit;
}

// If the installer has been used, but the directory still exists, show a warning.
if (file_exists('install.lock') && file_exists('installer/index.php')) {
    $warning = "<strong>Security Warning:</strong> The 'installer' directory still exists. Please delete it from your server immediately.";
}

// Optional: Set content type
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Placeholder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        .info {
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($warning)): ?>
            <div style="padding: 15px; background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $warning; ?>
            </div>
        <?php endif; ?>
        <h1>Website Placeholder</h1>
        <p>This is a placeholder page. Content will be added soon.</p>
        <div class="info">
            <p>File: index.php</p>
            <p><?php echo 'Current time: ' . date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
