<?php
// src/models/Product.php

class Product {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new product in the database.
     */
    public function create($name, $description, $price, $categoryId, $sellerId, $filePath) {
        $sql = "INSERT INTO products (name, description, price, category_id, seller_id, file_path)
                VALUES (:name, :description, :price, :category_id, :seller_id, :file_path)";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $categoryId,
                ':seller_id' => $sellerId,
                ':file_path' => $filePath,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Find a single product by its ID.
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name as category_name
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find all products by a specific seller.
     */
    public function findBySellerId($sellerId) {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC');
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    /**
     * Search for products based on various criteria.
     */
    public function search(array $criteria = []) {
        $sql = "SELECT p.*, c.name as category_name, AVG(r.rating) as avg_rating
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id";

        $where_clauses = [];
        $params = [];

        if (!empty($criteria['keyword'])) {
            $where_clauses[] = "(p.name LIKE :keyword OR p.description LIKE :keyword)";
            $params[':keyword'] = '%' . $criteria['keyword'] . '%';
        }

        if (!empty($criteria['category_id'])) {
            $where_clauses[] = "p.category_id = :category_id";
            $params[':category_id'] = $criteria['category_id'];
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $sql .= " GROUP BY p.id";

        if (!empty($criteria['min_rating'])) {
            $sql .= " HAVING avg_rating >= :min_rating";
            $params[':min_rating'] = $criteria['min_rating'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search query failed: " . $e->getMessage());
            return [];
        }
    }
}
