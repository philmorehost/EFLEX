<?php

// This script should be run to set up the database tables.
// It's recommended to delete this file after setup for security.

// In the next steps, I will create the config files.
// For now, I will define the connection details here.
// This allows this script to be run standalone.
$servername = "127.0.0.1"; // Using 127.0.0.1 instead of localhost to avoid potential DNS lookup issues
$username = "root";
$password = ""; // Assuming default XAMPP/WAMP password
$dbname = "cbt_platform";

try {
    // Establish connection to MySQL server
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE `$dbname`");

    echo "Database created or already exists. Now creating tables...<br>";

    // SQL to create tables
    $sql = "
    CREATE TABLE IF NOT EXISTS `roles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `role_name` VARCHAR(255) NOT NULL UNIQUE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `permission_name` VARCHAR(255) NOT NULL UNIQUE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `role_permissions` (
        `role_id` INT NOT NULL,
        `permission_id` INT NOT NULL,
        PRIMARY KEY (`role_id`, `permission_id`),
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_name` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `questions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `question_text` TEXT NOT NULL,
        `question_type` VARCHAR(50) NOT NULL DEFAULT 'multiple_choice',
        `options` TEXT,
        `correct_answer` TEXT NOT NULL,
        `category_id` INT,
        `created_by` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `tests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `test_name` VARCHAR(255) NOT NULL,
        `duration` INT NOT NULL,
        `created_by` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `test_questions` (
        `test_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        PRIMARY KEY (`test_id`, `question_id`),
        FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `user_tests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `test_id` INT NOT NULL,
        `score` DECIMAL(5, 2),
        `status` VARCHAR(50) NOT NULL,
        `start_time` DATETIME,
        `end_time` DATETIME,
        `extra_time_added` INT DEFAULT 0,
        `stop_reason` TEXT,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `user_answers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_test_id` INT NOT NULL,
        `question_id` INT NOT NULL,
        `selected_answer` TEXT,
        `is_correct` BOOLEAN,
        FOREIGN KEY (`user_test_id`) REFERENCES `user_tests`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `user_test_question` (`user_test_id`, `question_id`)
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `exam_schedules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `test_id` INT NOT NULL,
        `scheduled_time` DATETIME NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `admin_id` INT NOT NULL,
        `user_id` INT,
        `test_id` INT,
        `action` VARCHAR(255) NOT NULL,
        `details` TEXT,
        `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `message` TEXT NOT NULL,
        `is_read` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS `settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `setting_key` VARCHAR(255) NOT NULL UNIQUE,
        `setting_value` TEXT
    ) ENGINE=InnoDB;
    ";

    $conn->exec($sql);
    echo "All tables created successfully.<br>";

    // Insert default roles if they don't exist
    $conn->exec("INSERT IGNORE INTO `roles` (role_name) VALUES ('Super Admin'), ('Admin'), ('Staff'), ('User');");
    echo "Default roles ('Super Admin', 'Admin', 'Staff', 'User') created or already exist.<br>";

    // --- Create a default Super Admin user ---
    $admin_email = 'superadmin@example.com';
    $admin_password = 'password'; // NOTE: In a real-world scenario, a stronger default or a prompt would be better.

    // Get the Super Admin role ID
    $stmt = $conn->query("SELECT id FROM roles WHERE role_name = 'Super Admin'");
    $super_admin_role_id = $stmt->fetchColumn();

    if ($super_admin_role_id) {
        // Hash the password
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        // Use INSERT IGNORE to avoid errors on re-running the script
        $sql = "INSERT IGNORE INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['Super Admin', $admin_email, $hashed_password, $super_admin_role_id]);

        echo "Default Super Admin account created or already exists.<br>";
        echo "-> <strong>Email:</strong> " . htmlspecialchars($admin_email) . "<br>";
        echo "-> <strong>Password:</strong> " . htmlspecialchars($admin_password) . "<br>";
    } else {
        echo "<strong style='color:red;'>Could not find 'Super Admin' role to create default user.</strong><br>";
    }

    echo "<br><strong>Installation complete. Please delete this file for security.</strong>";

} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

$conn = null;
?>
