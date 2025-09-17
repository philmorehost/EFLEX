<?php
// src/controllers/CartController.php

require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        require_once __DIR__ . '/../views/cart.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); die('Invalid request method.');
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (!$productId) {
            Session::flash('error_message', 'Invalid Product.');
            header('Location: /products'); exit();
        }

        $productModel = new Product($this->pdo);
        $product = $productModel->findById($productId);

        if (!$product) {
            Session::flash('error_message', 'Product not found.');
            header('Location: /products'); exit();
        }

        if (!Session::has('cart')) {
            Session::set('cart', []);
        }

        $cart = Session::get('cart');

        if (isset($cart[$productId])) {
            Session::flash('success_message', 'Item is already in your cart.');
        } else {
            $cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
            Session::set('cart', $cart);
            Session::flash('success_message', 'Item added to cart!');
        }

        header('Location: /cart');
        exit();
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); die('Invalid request method.');
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if ($productId && Session::has('cart')) {
            $cart = Session::get('cart');
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                Session::set('cart', $cart);
                Session::flash('success_message', 'Item removed from cart.');
            }
        }

        header('Location: /cart');
        exit();
    }
}
