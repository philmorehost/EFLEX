<?php
// tests/bootstrap.php

// Require all the models and library files
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/models/Category.php';
require_once __DIR__ . '/../src/models/Order.php';
require_once __DIR__ . '/../src/models/License.php';
require_once __DIR__ . '/../lib/Session.php';

// This function will be used by tests to get a fresh in-memory database
// with the schema loaded.
function createTestDatabase() {
    try {
        // Use in-memory SQLite database for tests
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Read the schema file
        $sql = file_get_contents(__DIR__ . '/../database.sql');

        // SQLite does not support some MySQL-specific syntax.
        // We need to remove it before executing.
        // This is a bit brittle but good enough for this simulation.
        $sql = preg_replace('/ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/', ';', $sql);
        $sql = preg_replace('/AUTO_INCREMENT/', 'AUTOINCREMENT', $sql);
        $sql = preg_replace('/`/', '', $sql); // Remove backticks
        $sql = preg_replace('/enum\((.*?)\)/', 'TEXT', $sql); // Replace ENUM with TEXT
        $sql = preg_replace('/on DELETE (CASCADE|RESTRICT)/', '', $sql); // Remove ON DELETE clauses
        $sql = preg_replace('/on UPDATE (CASCADE|RESTRICT)/', '', $sql); // Remove ON UPDATE clauses

        // Execute the schema creation
        $pdo->exec($sql);

        return $pdo;
    } catch (PDOException $e) {
        die("Test database setup failed: " . $e->getMessage());
    }
}
