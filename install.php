<?php
$pageTitle = "CBT Platform Installation";
// We can't use the full header/footer as the DB isn't guaranteed to be there.
// This will be a standalone script.

// --- Basic Styling ---
$style = "
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; margin: 0; background-color: #f4f5f7; }
    .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1, h2 { color: #333; }
    .btn { display: inline-block; background-color: #007bff; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; }
    .btn-disabled { background-color: #ccc; cursor: not-allowed; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .alert-warning { color: #856404; background-color: #fff3cd; border-color: #ffeeba; }
    pre { background: #eee; padding: 10px; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; }
";

// --- Check if installation is already complete ---
$already_installed = false;
try {
    // Suppress warnings for the initial check, as DB/tables might not exist
    @require_once __DIR__ . '/includes/config.php';
    if (isset($conn) && $conn->query("SELECT 1 FROM `users` LIMIT 1")) {
        $already_installed = true;
    }
} catch (Exception $e) {
    // Ignore exceptions during check
}

$messages = [];

// --- Main Installation Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_installed) {
    if (!file_exists('database.sql')) {
        $messages[] = ['type' => 'danger', 'text' => '<b>Error:</b> `database.sql` file not found.'];
    } else {
        // Get SQL content
        $sql_content = file_get_contents('database.sql');

        // Execute the multi-query
        if ($conn->multi_query($sql_content)) {
            // Clear results from each query
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());

            $messages[] = ['type' => 'success', 'text' => 'Database tables created and seeded successfully!'];
            $messages[] = ['type' => 'warning', 'text' => '<strong>IMPORTANT:</strong> For security reasons, please delete this `install.php` file immediately.'];
            $already_installed = true; // Mark as installed for this request
        } else {
            $messages[] = ['type' => 'danger', 'text' => '<b>Database Error:</b> ' . $conn->error];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <style><?php echo $style; ?></style>
</head>
<body>
    <div class="container">
        <h1>CBT Platform Installation</h1>

        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($already_installed): ?>
            <div class="alert alert-success">
                The application appears to be already installed. The `users` table exists.
            </div>
            <div class="alert alert-warning">
                If you have not already done so, please delete `install.php` for security.
            </div>
        <?php else: ?>
            <p>Welcome! This script will set up the necessary database tables for the CBT platform.</p>
            <p>Please ensure you have correctly configured your database credentials in <code>includes/config.php</code> before proceeding.</p>
            <form action="install.php" method="POST">
                <button type="submit" class="btn">Install Database</button>
            </form>
        <?php endif; ?>

        <?php if ($already_installed && !empty($messages)): ?>
             <a href="index.php" class="btn" style="margin-top: 20px;">Go to Homepage</a>
        <?php endif; ?>
    </div>
</body>
</html>
