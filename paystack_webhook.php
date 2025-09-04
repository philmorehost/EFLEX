<?php
// This script handles secure, server-to-server communication from Paystack.
// It should not be browsed to directly by a user.

require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Get Paystack secret key from settings
$paystack_secret_key = get_app_setting('paystack_secret_key');

// Only process POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    http_response_code(400);
    exit();
}

// Get the request's body
$input = @file_get_contents("php://input");

// Validate event came from Paystack
if(!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $paystack_secret_key))){
  // Invalid request
  http_response_code(401);
  exit();
}

// Acknowledge receipt of the event
http_response_code(200);

$event = json_decode($input);

// Handle the charge.success event
if ($event && isset($event->event) && $event->event == 'charge.success') {
    $metadata = $event->data->metadata ?? null;
    $order_id = $metadata->order_id ?? 0;
    $user_id = $metadata->user_id ?? 0;

    if ($order_id > 0 && $user_id > 0) {
        // Use the centralized function to finalize the order
        // This will activate subscriptions and grant permissions.
        finalize_successful_order($order_id, $user_id);
    }
}

exit();
?>
