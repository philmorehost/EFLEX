<?php
// src/controllers/DashboardController.php

require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/Product.php';

class DashboardController {
    private $pdo;

    public function __conStrucT($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        $user_type = Session::get('user_type');
        $user_id = Session::get('user_id');

        $data = [
            'products' => [],
            'orders' => [],
            'user_type' => $user_type,
            'username' => Session::get('username')
        ];

        if ($user_type === 'seller') {
            $productModel = new Product($this->pdo);
            $data['products'] = $productModel->findBySellerId($user_id);
        } else { // 'buyer'
            $orderModel = new Order($this->pdo);
            $data['orders'] = $orderModel->findAllByBuyerId($user_id);
        }

        // Make data available to the view
        extract($data);

        // Load the view
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
