<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Get the reference from the POST request
$input = json_decode(file_get_contents('php://input'), true);
$reference = $input['reference'] ?? '';

if (empty($reference)) {
    echo json_encode(['status' => 'error', 'message' => 'No payment reference provided.']);
    exit;
}

// Get Paystack secret key from settings
$paystack_secret_key = get_app_setting('paystack_secret_key');
if (empty($paystack_secret_key)) {
    echo json_encode(['status' => 'error', 'message' => 'Payment gateway not configured.']);
    exit;
}

// Verify the transaction with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $paystack_secret_key]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response);

if ($result && $result->status && $result->data->status == 'success') {
    // Payment was successful.
    $metadata = $result->data->metadata;
    $order_id = $metadata->order_id ?? 0;
    // Use the session ID of the currently logged-in user for security.
    $user_id = $_SESSION['id'] ?? 0;

    if ($order_id > 0 && $user_id > 0) {
        // Use the centralized function to finalize the order
        $finalization_result = finalize_successful_order($order_id, $user_id);

        if ($finalization_result['status'] === 'success') {
            // Clear the cart
            $_SESSION['cart'] = [];
            echo json_encode(['status' => 'success', 'orderId' => $order_id]);
        } else {
            echo json_encode($finalization_result);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Transaction metadata is missing.']);
    }
} else {
    // Payment was not successful
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: ' . ($result->data->gateway_response ?? 'Unknown reason')]);
}
?>
