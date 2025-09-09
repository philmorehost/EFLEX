<?php
// config/database.php

// require the configuration file which holds the database credentials
require_once 'config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // The $pdo object is now ready to be used for database operations.
    return $pdo;
} catch (\PDOException $e) {
    // In a production environment, you would log this error and show a generic
    // error message to the user. For development, we can show the actual error.
    // The 'exit' call stops the script from running further if connection fails.
    exit('Database Connection Failed: ' . $e->getMessage());
}
?>
