<?php
// This script handles secure, server-to-server communication from Paystack.

require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Get Paystack secret key from settings
$paystack_secret_key = get_app_setting('paystack_secret_key');

// Only process POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    http_response_code(405); // Method Not Allowed
    exit();
}

// Get the request's body
$input = @file_get_contents("php://input");

// Validate that the event came from Paystack
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $paystack_secret_key))) {
    // Invalid request
    http_response_code(401); // Unauthorized
    exit();
}

// Acknowledge receipt of the event immediately to prevent Paystack from retrying.
http_response_code(200);

$event = json_decode($input);

// Process only successful charge events
if ($event && $event->event == 'charge.success') {
    $metadata = $event->data->metadata ?? null;
    $order_id = $metadata->order_id ?? null;
    $user_id = $metadata->user_id ?? null;

    if ($order_id && $user_id) {
        // Use the centralized, robust function to finalize the order.
        // This ensures consistent processing for both webhooks and browser callbacks.
        $finalization_result = finalize_successful_order((int)$order_id, (int)$user_id);

        if ($finalization_result['status'] !== 'success') {
            // The webhook has already told Paystack "200 OK", but we need to log the failure
            // so the site administrator can investigate.
            error_log(
                "Paystack webhook failed to finalize Order ID: $order_id for User ID: $user_id. Reason: " .
                ($finalization_result['message'] ?? 'Unknown')
            );
        }
    } else {
        // Log an error if the metadata is missing, which is essential for processing.
        error_log("Paystack webhook received 'charge.success' event with missing order_id or user_id in metadata. Input: " . $input);
    }
}

// Exit peacefully. The response code has already been set.
exit();
