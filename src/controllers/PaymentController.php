<?php
// src/controllers/PaymentController.php

require_once __DIR__ . '/../lib/Session.php';
require_once __DIR__ . '/../lib/PaystackClient.php';
require_once __DIR__ . '/../lib/FlutterwaveClient.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/License.php';

class PaymentController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function getOrderAndUser($orderId, $buyerId) {
        $orderModel = new Order($this->pdo);
        $order = $orderModel->findWithItems($orderId, $buyerId);
        if (!$order) {
            Session::flash('error_message', 'Order not found or access denied.');
            header('Location: /dashboard'); exit();
        }

        $userModel = new User($this->pdo);
        $user = $userModel->findById($buyerId);
        if (!$user) {
            Session::flash('error_message', 'User not found.');
            header('Location: /dashboard'); exit();
        }

        return ['order' => $order, 'user' => $user];
    }

    public function payWithPaystack() {
        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $buyerId = Session::get('user_id');
        $data = $this->getOrderAndUser($orderId, $buyerId);
        $reference = 'psk_' . $orderId . '_' . uniqid();
        $paystack = new PaystackClient('mock_api_key');
        $response = $paystack->initializeTransaction($data['user']['email'], $data['order']['total_amount'], $reference);
        header('Location: ' . $response['data']['authorization_url']);
        exit();
    }

    public function payWithFlutterwave() {
        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $buyerId = Session::get('user_id');
        $data = $this->getOrderAndUser($orderId, $buyerId);
        $tx_ref = 'flw_' . $orderId . '_' . uniqid();
        $flutterwave = new FlutterwaveClient('mock_api_key');
        $response = $flutterwave->initializeTransaction($data['user']['email'], $data['order']['total_amount'], $tx_ref);
        header('Location: ' . $response['data']['link']);
        exit();
    }

    public function mockPaymentConfirm() {
        require_once __DIR__ . '/../views/mock_payment_confirm.php';
    }

    public function webhook() {
        $ref = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);

        if (!$ref || $status !== 'success') {
            Session::flash('error_message', 'Invalid payment webhook data.');
            header('Location: /'); exit();
        }

        // Extract order ID from reference. e.g., "psk_123_abc" -> "123"
        preg_match('/_(\d+)_/', $ref, $matches);
        if (!isset($matches[1])) {
            Session::flash('error_message', 'Invalid payment reference.');
            header('Location: /'); exit();
        }
        $orderId = (int)$matches[1];

        $orderModel = new Order($this->pdo);
        // We don't check buyerId here because a webhook is anonymous.
        // In a real app, we'd use a signature to verify the webhook's authenticity.
        $order = $orderModel->findWithItems($orderId, Session::get('user_id')); // Still need buyerId for findWithItems signature

        if ($order && $order['status'] === 'pending') {
            $orderModel->updateStatus($orderId, 'completed');

            $licenseModel = new License($this->pdo);
            foreach ($order['items'] as $item) {
                $licenseKey = 'LICENSE-' . strtoupper(uniqid()) . '-' . $item['id'];
                $licenseModel->create($item['id'], $licenseKey);
            }

            Session::flash('success_message', 'Payment successful! Your order is complete.');
        } else {
            Session::flash('error_message', 'Order already processed or not found.');
        }

        header('Location: /dashboard');
        exit();
    }
}
