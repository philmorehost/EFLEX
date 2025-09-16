<?php
// src/models/Product.php

class Product {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new product in the database.
     *
     * @param string $name
     * @param string $description
     * @param float $price
     * @param int $categoryId
     * @param int $sellerId
     * @param string $filePath
     * @return bool
     */
    public function create($name, $description, $price, $categoryId, $sellerId, $filePath) {
        $sql = "INSERT INTO products (name, description, price, category_id, seller_id, file_path)
                VALUES (:name, :description, :price, :category_id, :seller_id, :file_path)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $categoryId,
                ':seller_id' => $sellerId,
                ':file_path' => $filePath,
            ]);
            return true;
        } catch (PDOException $e) {
            // In a real application, log this error.
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Find all products by a specific seller.
     *
     * @param int $sellerId
     * @return array An array of product data.
     */
    public function findBySellerId($sellerId) {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC');
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }
}
