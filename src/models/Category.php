<?php
// src/models/Category.php

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Find all categories.
     * @return array An array of all categories.
     */
    public function findAll() {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }
}
