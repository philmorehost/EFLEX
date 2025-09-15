<?php
$pageTitle = "CBT Platform Installation";
// This is a standalone script.

// --- Basic Styling ---
$style = "
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; margin: 0; background-color: #f4f5f7; }
    .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; }
    .btn { display: inline-block; background-color: #dc3545; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; font-weight: bold; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .alert-warning { color: #856404; background-color: #fff3cd; border-color: #ffeeba; }
    code { background: #eee; padding: 2px 4px; border-radius: 4px; }
";

$messages = [];
$config_exists = file_exists(__DIR__ . '/includes/config.php');

if ($config_exists) {
    @require_once __DIR__ . '/includes/config.php';
}

// --- Main Installation Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$config_exists || !isset($conn)) {
        $messages[] = ['type' => 'danger', 'text' => '<b>Error:</b> `includes/config.php` not found or database connection failed. Please configure it first.'];
    } elseif (!file_exists('database.sql')) {
        $messages[] = ['type' => 'danger', 'text' => '<b>Error:</b> `database.sql` file not found.'];
    } else {
        // --- Step 1: Drop existing tables ---
        $tables = [
            'student_answers', 'test_attempts', 'test_questions', 'tests', 'options', 'questions',
            'question_categories', 'role_permissions', 'permissions', 'permission_categories',
            'password_resets', 'users', 'roles', 'settings'
        ];

        $conn->query('SET foreign_key_checks = 0');
        foreach ($tables as $table) {
            $conn->query("DROP TABLE IF EXISTS `$table`");
        }
        $conn->query('SET foreign_key_checks = 1');

        $messages[] = ['type' => 'success', 'text' => 'Existing tables dropped successfully.'];

        // --- Step 2: Re-create tables from SQL file ---
        $sql_content = file_get_contents('database.sql');
        if ($conn->multi_query($sql_content)) {
            // Clear results from each query
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            $messages[] = ['type' => 'success', 'text' => 'Database tables created and seeded successfully!'];

            // --- Step 3: Programmatically set the Super Admin password ---
            $new_admin_password = 'password123';
            $hashed_password = password_hash($new_admin_password, PASSWORD_DEFAULT);

            $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = 1");
            $update_stmt->bind_param("s", $hashed_password);

            if ($update_stmt->execute()) {
                 $messages[] = ['type' => 'success', 'text' => 'Super Admin password has been reset.'];
            } else {
                 $messages[] = ['type' => 'danger', 'text' => 'Could not reset Super Admin password.'];
            }
            $update_stmt->close();

            $messages[] = ['type' => 'warning', 'text' => '<strong>IMPORTANT:</strong> For security reasons, please DELETE THIS `install.php` FILE immediately.'];

        } else {
            $messages[] = ['type' => 'danger', 'text' => '<b>Database Error during creation:</b> ' . $conn->error];
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
        <h1>CBT Platform Re-Installation</h1>

        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-danger">
                <strong>WARNING:</strong> This script will completely wipe and re-create all CBT platform tables in your database (<code><?php echo defined('DB_NAME') ? DB_NAME : 'N/A'; ?></code>).
                <br><strong>Any existing data will be permanently lost.</strong>
            </div>
            <p>This process is necessary to update the database schema to the latest version and fix any installation issues.</p>
             <?php if (!$config_exists): ?>
                 <div class="alert alert-danger">The <code>includes/config.php</code> file could not be found. Please ensure it exists and contains the correct database credentials.</div>
             <?php else: ?>
                <form action="install.php" method="POST" onsubmit="return confirm('Are you absolutely sure you want to wipe all data and reinstall?');">
                    <button type="submit" class="btn">Wipe Data & Reinstall</button>
                </form>
             <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($messages) && strpos(end($messages)['text'], 'DELETE THIS') !== false): ?>
             <div class="alert alert-info">
                 <p class="mb-0"><strong>Login with the new Super Admin credentials:</strong></p>
                 <p class="mb-0"><strong>Email:</strong> <code>superadmin@cbt.com</code></p>
                 <p class="mb-0"><strong>Password:</strong> <code>password123</code></p>
             </div>
             <a href="login.php" style="display:inline-block; margin-top: 20px; background-color: #28a745;" class="btn">Go to Login Page</a>
        <?php endif; ?>
    </div>
</body>
</html>
