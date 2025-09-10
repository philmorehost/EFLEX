<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Get parameters from the redirect URL
$transaction_id = $_GET['transaction_id'] ?? null;
$tx_ref = $_GET['tx_ref'] ?? null;
$status = $_GET['status'] ?? null;

// Basic validation
if (empty($transaction_id) || empty($tx_ref) || $status !== 'successful') {
    $_SESSION['error_message'] = "Payment was not successful or the transaction reference is missing.";
    header("location: cart.php");
    exit;
}

// --- Verify the transaction with Flutterwave ---
$flutterwave_secret_key = get_app_setting('flutterwave_secret_key');
if (empty($flutterwave_secret_key)) {
    die('Flutterwave payment gateway is not configured.');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.flutterwave.com/v3/transactions/' . $transaction_id . '/verify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $flutterwave_secret_key]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response);

if ($result && $result->status === 'success' && $result->data->status === 'successful') {
    // --- Further verification ---
    // 1. Get our order details from the database using the tx_ref
    $sql_order = "SELECT id, user_id, total_amount FROM orders WHERE transaction_ref = ?";
    $stmt_order = $mysqli->prepare($sql_order);
    $stmt_order->bind_param("s", $tx_ref);
    $stmt_order->execute();
    $order_data = $stmt_order->get_result()->fetch_assoc();
    $stmt_order->close();

    if (!$order_data) {
        die("Order not found for this transaction reference.");
    }

    // 2. Check if the amount and currency match
    $amount_from_flutterwave = $result->data->amount;
    $currency_from_flutterwave = $result->data->currency;
    $amount_from_db = $order_data['total_amount'];
    $currency_from_db = get_app_setting('currency_code', 'NGN');

    if ($amount_from_flutterwave < $amount_from_db || $currency_from_flutterwave !== $currency_from_db) {
        // Amount mismatch, this could be a security issue.
        die("Transaction amount or currency mismatch. Please contact support.");
    }

    // --- All checks passed, finalize the order ---
    $order_id = $order_data['id'];
    $user_id = $order_data['user_id'];

    $finalization_result = finalize_successful_order($order_id, $user_id);

    if ($finalization_result['status'] === 'success') {
        // Clear the cart and redirect to success page
        unset($_SESSION['cart']);
        header("location: order_success.php?order_id=" . $order_id);
        exit();
    } else {
        // The finalization failed, this is a critical error.
        die("A critical error occurred while finalizing your order. Please contact support with your Order ID: #$order_id");
    }

} else {
    // Payment verification failed with Flutterwave
    $_SESSION['error_message'] = 'Payment verification failed. Please contact support.';
    header("location: cart.php");
    exit;
}
?>
