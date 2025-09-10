<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Check if user is logged in and cart is not empty
if (!isset($_SESSION["loggedin"]) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("location: login.php");
    exit;
}

// Fetch Flutterwave keys from settings
$flutterwave_secret_key = get_app_setting('flutterwave_secret_key');
if (empty($flutterwave_secret_key)) {
    die('Flutterwave payment gateway is not configured. Please contact support.');
}

// Get order details from session variables
$total_amount = $_SESSION['total_amount'] ?? 0;
$user_email = $_SESSION['email'] ?? '';
$user_id = $_SESSION['id'] ?? 0;
$order_id = $_SESSION['latest_order_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Customer';

if ($total_amount <= 0 || empty($user_email) || $order_id == 0 || $user_id == 0) {
    $_SESSION['error_message'] = "Your session expired or essential information is missing. Please try again.";
    header("location: cart.php");
    exit;
}

// The callback URL is where Flutterwave redirects the user after payment
$redirect_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/flutterwave_callback.php';

// Create a unique transaction reference
$tx_ref = 'eflex-' . $order_id . '-' . time();

$post_data = [
    'tx_ref' => $tx_ref,
    'amount' => $total_amount,
    'currency' => get_app_setting('currency_code', 'NGN'), // Default to NGN if not set
    'redirect_url' => $redirect_url,
    'customer' => [
        'email' => $user_email,
        'name' => $username,
    ],
    'meta' => [
        'order_id' => $order_id,
        'user_id' => $user_id
    ],
    'customizations' => [
        'title' => get_app_setting('site_title', 'Eflex E-commerce'),
        'description' => 'Payment for Order #' . $order_id,
    ]
];

// Update the order with the transaction reference
$stmt_update_ref = $mysqli->prepare("UPDATE orders SET transaction_ref = ? WHERE id = ?");
$stmt_update_ref->bind_param("si", $tx_ref, $order_id);
$stmt_update_ref->execute();
$stmt_update_ref->close();


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.flutterwave.com/v3/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $flutterwave_secret_key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die('cURL Error: ' . $error);
}

$result = json_decode($response);

if ($result && $result->status === 'success') {
    // Redirect user to Flutterwave payment page
    header('Location: ' . $result->data->link);
    exit;
} else {
    $message = 'Flutterwave API Error: ' . ($result->message ?? 'An unknown error occurred.');
    die($message);
}
?>
