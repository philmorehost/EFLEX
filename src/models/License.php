<?php
// src/models/License.php

class License {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new license for an order item.
     *
     * @param int $orderItemId
     * @param string $licenseKey
     * @return bool
     */
    public function create($orderItemId, $licenseKey) {
        $sql = "INSERT INTO licenses (order_item_id, license_key) VALUES (?, ?)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$orderItemId, $licenseKey]);
        } catch (PDOException $e) {
            error_log("License creation failed: " . $e->getMessage());
            return false;
        }
    }
}
