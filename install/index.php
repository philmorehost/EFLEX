<?php
// install/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// --- Security Check ---
if (file_exists(__DIR__ . '/../config/config.php') && filesize(__DIR__ . '/../config/config.php') > 0) {
    if (!isset($_GET['override'])) {
        die("Installer is locked. To re-run, please delete or empty `config/config.php`.");
    }
}

// --- Step Management ---
$step = $_GET['step'] ?? 1;

switch ($step) {
    case 1:
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 8.0',
                'check' => version_compare(PHP_VERSION, '8.0.0', '>='),
                'current' => PHP_VERSION,
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'check' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed',
            ],
        ];
        $all_ok = !in_array(false, array_column($requirements, 'check'), true);
        include 'views/step1_welcome.php';
        break;

    case 2:
        include 'views/step2_database.php';
        break;

    case 3:
        // Process the installation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die('Invalid request.'); }

        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        $admin_email = $_POST['admin_email'];
        $admin_password = $_POST['admin_password'];

        // 1. Test Database Connection
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            header('Location: index.php?step=2&error=' . urlencode('Database connection failed: ' . $e->getMessage()));
            exit();
        }

        // 2. Write config file
        $config_content = "<?php
// config/config.php
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_CHARSET', 'utf8mb4');
";
        if (file_put_contents(__DIR__ . '/../config/config.php', $config_content) === false) {
            header('Location: index.php?step=2&error=' . urlencode('Could not write to config file. Please check permissions.'));
            exit();
        }

        // 3. Import database schema
        try {
            $sql = file_get_contents(__DIR__ . '/../database.sql');
            $pdo->exec($sql);
        } catch (Exception $e) {
            header('Location: index.php?step=2&error=' . urlencode('Database import failed: ' . $e->getMessage()));
            exit();
        }

        // 4. Create admin user
        try {
            $passwordHash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, 'seller')");
            $stmt->execute(['admin', $admin_email, $passwordHash]);
        } catch (Exception $e) {
            header('Location: index.php?step=2&error=' . urlencode('Admin user creation failed: ' . $e->getMessage()));
            exit();
        }

        // 5. Store admin details for success page and redirect
        $_SESSION['admin_email'] = $admin_email;
        $_SESSION['admin_password'] = $admin_password;
        header('Location: index.php?step=4');
        exit();

    case 4:
        // Success Page
        include 'views/step3_success.php';
        break;

    default:
        include 'views/step1_welcome.php';
        break;
}
