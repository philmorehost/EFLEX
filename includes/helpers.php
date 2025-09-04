<?php
// A place for helper functions that can be used across the application.
require_once 'google_drive_api.php'; // For granting file permissions

// Global variable to cache settings so we don't query the DB repeatedly.
$app_settings = null;

function load_app_settings() {
    global $app_settings, $mysqli;

    // Only load settings if they haven't been loaded yet.
    if ($app_settings === null) {
        $app_settings = [];
        $sql = "SELECT setting_key, setting_value FROM settings";
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $app_settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

function get_app_setting($key, $default = '') {
    global $app_settings;

    // Ensure settings are loaded.
    if ($app_settings === null) {
        load_app_settings();
    }

    return $app_settings[$key] ?? $default;
}

function format_price($price) {
    $symbol = get_app_setting('currency_symbol', '$');
    // number_format adds commas and ensures two decimal places.
    return $symbol . number_format((float)$price, 2);
}

function finalize_successful_order($order_id, $user_id) {
    global $mysqli;

    // 1. Update the main order status to 'Completed'
    $sql_order = "UPDATE orders SET status = 'Completed' WHERE id = ? AND status = 'Pending'";
    if($stmt_order = $mysqli->prepare($sql_order)){
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        // If no rows were affected, the order was likely already processed.
        if($stmt_order->affected_rows == 0) {
            $stmt_order->close();
            return ['status' => 'success', 'message' => 'Order already processed.'];
        }
        $stmt_order->close();
    }

    // 2. Fetch user's email for Google Drive permissions
    $user_email = '';
    $sql_user = "SELECT email FROM users WHERE id = ?";
    if($stmt_user = $mysqli->prepare($sql_user)){
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->bind_result($user_email);
        $stmt_user->fetch();
        $stmt_user->close();
    }

    if (empty($user_email)) {
        // This would be a critical error, log it.
        return ['status' => 'error', 'message' => 'Could not find user email for permission granting.'];
    }

    // 3. Fetch the items from the order to activate subscriptions
    $sql_items = "SELECT product_id FROM order_items WHERE order_id = ?";
    if($stmt_items = $mysqli->prepare($sql_items)){
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        while($item = $result_items->fetch_assoc()){
            $product_id = $item['product_id'];

            // Check if this product is a subscription (has GDrive files) and get duration
            $sql_product = "SELECT p.duration_days FROM products p JOIN product_google_drive_files pgdf ON p.id = pgdf.product_id WHERE p.id = ? GROUP BY p.id";
            if($stmt_prod = $mysqli->prepare($sql_product)){
                $stmt_prod->bind_param("i", $product_id);
                $stmt_prod->execute();
                $result_prod = $stmt_prod->get_result();

                if($prod_details = $result_prod->fetch_assoc()){
                    // This is a subscription product. Activate it.
                    $duration_days = (int)$prod_details['duration_days'];
                    $expires_at = $duration_days > 0 ? date('Y-m-d H:i:s', strtotime("+$duration_days days")) : null;

                    // Add the new subscription to the user_subscriptions table
                    $sql_insert_sub = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";
                    if($stmt_insert = $mysqli->prepare($sql_insert_sub)){
                        $stmt_insert->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                    }

                    // Grant Google Drive permissions for this product
                    $file_ids = [];
                    $sql_files = "SELECT google_drive_file_id FROM product_google_drive_files WHERE product_id = ?";
                    if($stmt_files = $mysqli->prepare($sql_files)){
                        $stmt_files->bind_param("i", $product_id);
                        $stmt_files->execute();
                        $result_files_perm = $stmt_files->get_result();
                        while($row = $result_files_perm->fetch_assoc()){
                            $file_ids[] = $row['google_drive_file_id'];
                        }
                        $stmt_files->close();
                    }

                    foreach($file_ids as $file_id){
                        grant_file_permission($file_id, $user_email);
                    }
                }
                $stmt_prod->close();
            }
        }
        $stmt_items->close();
    }

    return ['status' => 'success', 'message' => 'Order finalized successfully.'];
}
?>
