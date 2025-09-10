<?php
// --- DEBUGGING START ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- DEBUGGING END ---

// Check if this is an installation request
if (isset($_GET['install'])) {
    // --- Run Installer ---
    // The entire installer UI and logic is now part of this file.
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CBT Platform Installation</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background-color: #f0f2f5; }
            .installer-container { max-width: 800px; margin: 50px auto; }
            .log { background-color: #212529; color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9rem; height: 400px; overflow-y: scroll; white-space: pre-wrap; }
        </style>
    </head>
    <body>
        <div class="installer-container">
            <div class="card shadow">
                <div class="card-header text-center"><h1>CBT Platform Installation</h1></div>
                <div class="card-body">
                    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                        <p class="lead">This will set up the necessary database and tables.</p>
                        <p><strong>Warning:</strong> Please ensure your database credentials in `config/config.php` are correct before proceeding.</p>
                        <div class="d-grid">
                            <form action="index.php?install=true" method="POST">
                                <button type="submit" class="btn btn-primary btn-lg">Start Installation</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="log">
                            <?php
                            // --- Start Installation Logic ---
                            require_once __DIR__ . '/../config/config.php';
                            $servername = DB_HOST; $username = DB_USER; $password = DB_PASS; $dbname = DB_NAME;

                            try {
                                echo "Attempting to connect to MySQL server...\n";
                                $conn = new PDO("mysql:host=$servername", $username, $password);
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                echo "<span class='text-success'>Connection successful.</span>\n\n";

                                echo "Creating database '$dbname' if it doesn't exist...\n";
                                $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                                $conn->exec("USE `$dbname`");
                                echo "<span class='text-success'>Database is ready.</span>\n\n";

                                echo "Starting table creation...\n";
                                $sql = "CREATE TABLE IF NOT EXISTS `roles` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `role_name` VARCHAR(255) NOT NULL UNIQUE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `users` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL UNIQUE, `password` VARCHAR(255) NOT NULL, `role_id` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `permissions` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `permission_name` VARCHAR(255) NOT NULL UNIQUE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `role_permissions` ( `role_id` INT NOT NULL, `permission_id` INT NOT NULL, PRIMARY KEY (`role_id`, `permission_id`), FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE, FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `categories` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `category_name` VARCHAR(255) NOT NULL ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `questions` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `question_text` TEXT NOT NULL, `question_type` VARCHAR(50) NOT NULL DEFAULT 'multiple_choice', `options` TEXT, `correct_answer` TEXT NOT NULL, `category_id` INT, `created_by` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL, FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `tests` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `test_name` VARCHAR(255) NOT NULL, `duration` INT NOT NULL, `created_by` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `test_questions` ( `test_id` INT NOT NULL, `question_id` INT NOT NULL, PRIMARY KEY (`test_id`, `question_id`), FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE, FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `user_tests` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `test_id` INT NOT NULL, `score` DECIMAL(5, 2), `status` VARCHAR(50) NOT NULL, `start_time` DATETIME, `end_time` DATETIME, `extra_time_added` INT DEFAULT 0, `stop_reason` TEXT, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `user_answers` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_test_id` INT NOT NULL, `question_id` INT NOT NULL, `selected_answer` TEXT, `is_correct` BOOLEAN, FOREIGN KEY (`user_test_id`) REFERENCES `user_tests`(`id`) ON DELETE CASCADE, FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE, UNIQUE KEY `user_test_question` (`user_test_id`, `question_id`) ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `exam_schedules` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `test_id` INT NOT NULL, `scheduled_time` DATETIME NOT NULL, `status` VARCHAR(50) NOT NULL DEFAULT 'pending', FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `activity_logs` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `admin_id` INT NOT NULL, `user_id` INT, `test_id` INT, `action` VARCHAR(255) NOT NULL, `details` TEXT, `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL, FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE SET NULL ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `notifications` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT, `message` TEXT NOT NULL, `is_read` BOOLEAN DEFAULT FALSE, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `settings` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `setting_key` VARCHAR(255) NOT NULL UNIQUE, `setting_value` TEXT ) ENGINE=InnoDB; CREATE TABLE IF NOT EXISTS `password_resets` ( `id` INT AUTO_INCREMENT PRIMARY KEY, `email` VARCHAR(255) NOT NULL, `token` VARCHAR(255) NOT NULL, `expires_at` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX `email_index` (`email`) ) ENGINE=InnoDB;";
                                $conn->exec($sql);
                                echo "<span class='text-success'>All tables created successfully.</span>\n\n";

                                echo "Inserting default data...\n";
                                $conn->exec("INSERT IGNORE INTO `roles` (role_name) VALUES ('Super Admin'), ('Admin'), ('Staff'), ('User');");
                                echo "  - Default roles created.\n";
                                $admin_email = 'superadmin@example.com'; $admin_password = 'password';
                                $stmt = $conn->query("SELECT id FROM roles WHERE role_name = 'Super Admin'");
                                $super_admin_role_id = $stmt->fetchColumn();
                                if ($super_admin_role_id) {
                                    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                                    $stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
                                    $stmt->execute(['Super Admin', $admin_email, $hashed_password, $super_admin_role_id]);
                                    echo "  - Default Super Admin account created.\n";
                                    echo "    <span class='text-warning'>Email:</span> superadmin@example.com\n";
                                    echo "    <span class='text-warning'>Password:</span> password\n";
                                } else {
                                    echo "<span class='text-danger'>Could not find 'Super Admin' role.</span>\n";
                                }
                                echo "<span class='text-success'>Default data inserted.</span>\n\n";
                                echo "<strong class='text-success'>INSTALLATION COMPLETE!</strong>\n";
                                echo "<p>You can now <a href='index.php'>go to the main page</a> to log in.</p>";
                            } catch(PDOException $e) {
                                echo "<span class='text-danger'>An error occurred:\n" . $e->getMessage() . "</span>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(); // Stop execution after installer runs
}

// --- Normal Page Logic (if not installing) ---
if (!@include_once __DIR__ . '/../config/config.php') {
    die('<h1>Error</h1><p>The main configuration file (config/config.php) is missing. Please create it from config.example.php and fill in your database details.</p>');
}

$setupNeeded = false;
try {
    $pdo = require ROOT_PATH . '/config/database.php';
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $setupNeeded = true;
    }
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $setupNeeded = true;
    } else {
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
    <style> body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f0f2f5; color: #333; } .container { max-width: 800px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; } h1 { color: #1d2129; } p { color: #4b4f56; } .alert { padding: 15px; margin-top: 20px; border-radius: 5px; } .alert-warning { background-color: #fffbe6; border: 1px solid #ffe58f; } .alert-success { background-color: #e9f5e9; border: 1px solid #a8d5a8; } a { color: #007bff; text-decoration: none; } a:hover { text-decoration: underline; } </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the CBT Platform</h1>
        <?php if ($setupNeeded): ?>
            <div class="alert alert-warning">
                <h2>Initial Setup Required</h2>
                <p>The database is not yet configured. Please run the installation script.</p>
                <p>Click the link below to begin:</p>
                <h3><a href="index.php?install=true">Run Installation Script</a></h3>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h2>System Ready</h2>
                <p>The application is configured correctly. Please <a href="login.php">login</a> to continue.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
