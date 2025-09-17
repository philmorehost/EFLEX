<?php
// src/models/Order.php

class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new order and its items in the database.
     * This should be done in a transaction.
     *
     * @param int $buyerId
     * @param array $cartItems
     * @param float $totalAmount
     * @return int|false The new order ID on success, false on failure.
     */
    public function create($buyerId, $cartItems, $totalAmount) {
        $this->pdo->beginTransaction();

        try {
            // 1. Create the main order record
            $sqlOrder = "INSERT INTO orders (buyer_id, total_amount, status) VALUES (?, ?, 'pending')";
            $stmtOrder = $this->pdo->prepare($sqlOrder);
            $stmtOrder->execute([$buyerId, $totalAmount]);
            $orderId = $this->pdo->lastInsertId();

            // 2. Create the order item records
            $sqlItems = "INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)";
            $stmtItems = $this->pdo->prepare($sqlItems);

            foreach ($cartItems as $item) {
                $stmtItems->execute([
                    $orderId,
                    $item['id'],
                    $item['price']
                ]);
            }

            // 3. Commit the transaction
            $this->pdo->commit();

            return $orderId;

        } catch (PDOException $e) {
            // If anything fails, roll back the transaction
            $this->pdo->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find an order and its items by the order ID.
     *
     * @param int $orderId
     * @param int $buyerId
     * @return array|false
     */
    public function findWithItems($orderId, $buyerId) {
        // We include buyerId to ensure a user can only see their own orders.
        $sql = "SELECT * FROM orders WHERE id = ? AND buyer_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId, $buyerId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        // Now fetch the items
        $sqlItems = "SELECT oi.*, p.name
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = ?";
        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->execute([$orderId]);
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    /**
     * Update the status of an order.
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatus($orderId, $status) {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Find all orders by a specific buyer.
     *
     * @param int $buyerId
     * @return array
     */
    public function findAllByBuyerId($buyerId) {
        $sql = "SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll();
    }
}
