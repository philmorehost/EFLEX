<?php
// public/index.php - Main Application Entry Point

// --- Initial Setup and Configuration ---

// Include the master configuration file.
// The '@' suppresses errors if the file doesn't exist, which we'll handle gracefully.
if (!@include_once __DIR__ . '/../config/config.php') {
    die('<h1>Error</h1><p>The main configuration file (config/config.php) is missing. Please ensure it exists.</p>');
}

// --- Database Connection and Setup Check ---

$pdo = null;
$setupNeeded = false;

try {
    // Attempt to connect to the database
    $pdo = require ROOT_PATH . '/config/database.php';

    // Check if the 'users' table exists as an indicator of a successful installation.
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $setupNeeded = true;
    }

} catch (PDOException $e) {
    // If the error indicates an "Unknown database", it means the DB hasn't been created yet.
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $setupNeeded = true;
    } else {
        // For any other database error, display a generic error message.
        // In a real app, you'd log the detailed error from $e->getMessage().
        die('<h1>Database Error</h1><p>A database connection error occurred. Please check your settings in config.php.</p><p><small>Error details: ' . $e->getMessage() . '</small></p>');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT Platform</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f0f2f5; color: #333; }
        .container { max-width: 800px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #1d2129; }
        p { color: #4b4f56; }
        .alert { padding: 15px; margin-top: 20px; border-radius: 5px; }
        .alert-warning { background-color: #fffbe6; border: 1px solid #ffe58f; }
        .alert-success { background-color: #e9f5e9; border: 1px solid #a8d5a8; }
        code { background-color: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the CBT Platform</h1>

        <?php if ($setupNeeded): ?>
            <div class="alert alert-warning">
                <h2>Initial Setup Required</h2>
                <p>The database is not yet configured. Please run the installation script to set up the necessary tables.</p>
                <p>Click the link below to begin:</p>
                <?php
                    // Construct the URL to the install script.
                    // It's in a sibling directory to 'public', so we need to construct the path carefully.
                    $install_url = str_replace("public/", "database/install.php", (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                ?>
                <h3><a href="<?php echo rtrim(str_replace('index.php', '', BASE_URL), '/') . '/database/install.php'; ?>">Run Installation Script</a></h3>
                <p><small>For security, please delete <code>database/install.php</code> after setup is complete.</small></p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h2>System Ready</h2>
                <p>The application is configured correctly and connected to the database.</p>
                <p>The CBT Platform is ready to be developed further!</p>
            </div>
            <?php
                // This is where the application's router would be included.
                // e.g., require_once ROOT_PATH . '/app/router.php';
            ?>
        <?php endif; ?>

    </div>
</body>
</html>
