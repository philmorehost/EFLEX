<?php
// src/controllers/DownloadController.php

require_once __DIR__ . '/../lib/Session.php';

class DownloadController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function download() {
        $orderItemId = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
        $buyerId = Session::get('user_id');

        if (!$orderItemId || !$buyerId) {
            http_response_code(403);
            die('Forbidden');
        }

        // This is a complex query to ensure the user has the right to download this file.
        $sql = "SELECT p.file_path
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.id = ? AND o.buyer_id = ? AND o.status = 'completed'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderItemId, $buyerId]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            die('File not found or you do not have permission to download it.');
        }

        $filePath = __DIR__ . '/../../uploads/' . $product['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found on server.');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        flush(); // Flush system output buffer
        readfile($filePath);
        exit();
    }
}
