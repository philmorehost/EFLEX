<?php
// src/controllers/OrderController.php

require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/Order.php';

class OrderController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function store() {
        if (!Session::has('cart') || empty(Session::get('cart'))) {
            Session::flash('error_message', 'Your cart is empty.');
            header('Location: /cart'); exit();
        }

        $cart = Session::get('cart');
        $buyerId = Session::get('user_id');

        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'];
        }

        $orderModel = new Order($this->pdo);
        $orderId = $orderModel->create($buyerId, $cart, $totalAmount);

        if ($orderId) {
            Session::remove('cart');
            header('Location: /checkout?order_id=' . $orderId);
            exit();
        } else {
            Session::flash('error_message', 'Could not create your order. Please try again.');
            header('Location: /cart');
            exit();
        }
    }

    public function checkout() {
        $orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
        $buyerId = Session::get('user_id');

        if (!$orderId) {
            http_response_code(404);
            die("Order not found.");
        }

        $orderModel = new Order($this->pdo);
        $orderData = $orderModel->findWithItems($orderId, $buyerId);

        if (!$orderData) {
            http_response_code(404);
            die("Order not found or you do not have permission to view it.");
        }

        $data = [
            'order' => $orderData,
            'orderItems' => $orderData['items']
        ];

        extract($data);

        require_once __DIR__ . '/../views/checkout.php';
    }

    public function details() {
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $buyerId = Session::get('user_id');

        if (!$orderId) {
            http_response_code(404);
            die("Order not found.");
        }

        $orderModel = new Order($this->pdo);
        $order = $orderModel->findWithItems($orderId, $buyerId);

        if (!$order || $order['status'] !== 'completed') {
            http_response_code(404);
            die("Order not found or not completed.");
        }

        $data = ['order' => $order];
        extract($data);

        require_once __DIR__ . '/../views/order_details.php';
    }
}
