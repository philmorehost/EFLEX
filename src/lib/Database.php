<?php
// src/lib/Database.php

// Require the config file once
require_once __DIR__ . '/../../config/config.php';

class Database {
    // Hold the class instance.
    private static $instance = null;
    private $conn;

    // The constructor is private to prevent initiation with new.
    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In a real application, you would log this error and show a generic message.
            // For development, it's fine to show the error.
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    // The singleton method
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
