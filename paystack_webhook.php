<?php
// This script handles secure, server-to-server communication from Paystack.
// It should not be browsed to directly by a user.

require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Get Paystack secret key from settings
$paystack_secret_key = get_app_setting('paystack_secret_key');

// Only process POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
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

if ($event && $event->event == 'charge.success') {
    $metadata = $event->data->metadata;
    $order_id = $metadata->order_id;
    $user_id = $metadata->user_id;

    // First, update the main order status to 'Completed'
    $sql_order = "UPDATE orders SET status = 'Completed' WHERE id = ?";
    if($stmt_order = $mysqli->prepare($sql_order)){
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $stmt_order->close();
    }

    // Fetch the items from the order to find out which subscription classes were purchased
    $sql_items = "SELECT product_id FROM order_items WHERE order_id = ?";
    if($stmt_items = $mysqli->prepare($sql_items)){
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        while($item = $result_items->fetch_assoc()){
            $product_id = $item['product_id'];

            // For each item, get its duration to calculate the expiry date
            $sql_product = "SELECT duration_days FROM products WHERE id = ? AND google_drive_folder_id IS NOT NULL AND google_drive_folder_id != ''";
            if($stmt_prod = $mysqli->prepare($sql_product)){
                $stmt_prod->bind_param("i", $product_id);
                $stmt_prod->execute();
                $result_prod = $stmt_prod->get_result();

                if($prod_details = $result_prod->fetch_assoc()){
                    // This is a subscription class. Activate it.
                    $duration_days = (int)$prod_details['duration_days'];
                    $expires_at = date('Y-m-d H:i:s', strtotime("+$duration_days days"));

                    // Add the new subscription to the user_subscriptions table
                    $sql_insert_sub = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";
                    if($stmt_insert = $mysqli->prepare($sql_insert_sub)){
                        $stmt_insert->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                    }
                }
                $stmt_prod->close();
            }
        }
        $stmt_items->close();
    }
}

exit();
?>
