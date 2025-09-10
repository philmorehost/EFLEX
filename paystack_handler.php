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

// Fetch Paystack secret key from settings
$paystack_secret_key = get_app_setting('paystack_secret_key');

if (empty($paystack_secret_key)) {
    die('Paystack payment gateway is not configured. Please contact support.');
}

// Get order details from session variables set during checkout
$total_amount = $_SESSION['total_amount'] ?? 0;
$user_email = $_SESSION['email'] ?? '';
$order_id = $_SESSION['latest_order_id'] ?? 0;

if ($total_amount <= 0 || empty($user_email) || $order_id == 0) {
    // Redirect to cart with an error if essential info is missing
    $_SESSION['error_message'] = "Your session expired or cart is empty. Please try again.";
    header("location: cart.php");
    exit;
}


// The callback URL is where Paystack redirects the user's browser after payment
$user_id = $_SESSION['id'];
$callback_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/order_success.php?order_id=' . $order_id . '&user_id=' . $user_id;

// Get customer details from session
$checkout_details = $_SESSION['checkout_details'] ?? [];
$full_name = trim(($checkout_details['first_name'] ?? '') . ' ' . ($checkout_details['last_name'] ?? ''));

$post_data = [
    'email' => $user_email,
    'amount' => $total_amount * 100, // Paystack requires amount in kobo/cents
    'callback_url' => $callback_url,
    'metadata' => [
        'order_id' => $order_id,
        'user_id' => $_SESSION['id'],
        'full_name' => $full_name,
        'custom_fields' => [
            [
                'display_name' => "Full Name",
                'variable_name' => "full_name",
                'value' => $full_name
            ],
            [
                'display_name' => "Address",
                'variable_name' => "address",
                'value' => $checkout_details['address'] ?? ''
            ]
        ]
    ]
];

// Clean up the session data after using it
unset($_SESSION['checkout_details']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $paystack_secret_key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die('cURL Error: ' . $error);
}

$result = json_decode($response);

if ($result && $result->status) {
    // Redirect user to Paystack payment page
    header('Location: ' . $result->data->authorization_url);
    exit;
} else {
    // Handle API error
    $message = 'Paystack API Error: ' . ($result->message ?? 'An unknown error occurred.');
    // In a real app, you'd log this and show a user-friendly error page.
    die($message);
}
?>
