<?php
// A place for helper functions that can be used across the application.

// Global variable to cache settings so we don't query the DB repeatedly.
$app_settings = null;

function load_app_settings() {
    global $app_settings, $mysqli;

    // Only load settings if they haven't been loaded yet.
    if ($app_settings === null) {
        $app_settings = [];
        $sql = "SELECT setting_key, setting_value FROM settings";
        $result = $mysqli->query($sql);
        if($result) {
            while ($row = $result->fetch_assoc()) {
                $app_settings[$row['setting_key']] = $row['setting_value'];
            }
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

// This function is now simplified. It finalizes an order by creating the subscription records.
// File access is handled by view_file.php, not by granting permissions.
function finalize_successful_order($order_id, $user_id) {
    global $mysqli;

    $mysqli->begin_transaction();
    try {
        // 1. Update the main order status to 'Completed'
        $sql_order = "UPDATE orders SET status = 'Completed' WHERE id = ? AND status = 'Pending'";
        $stmt_order = $mysqli->prepare($sql_order);
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        if ($stmt_order->affected_rows == 0) {
            $stmt_order->close();
            $mysqli->commit(); // Commit even if already processed
            return ['status' => 'success', 'message' => 'Order already processed.'];
        }
        $stmt_order->close();

        // 2. Fetch the items from the order to activate subscriptions
        $sql_items = "SELECT oi.product_id, p.duration_days
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        $stmt_items = $mysqli->prepare($sql_items);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        while ($item = $result_items->fetch_assoc()) {
            $product_id = $item['product_id'];
            $duration_days = (int)$item['duration_days'];
            $expires_at = $duration_days > 0 ? date('Y-m-d H:i:s', strtotime("+$duration_days days")) : null;

            // Add the new subscription to the user_subscriptions table
            $sql_insert_sub = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";
            $stmt_insert = $mysqli->prepare($sql_insert_sub);
            $stmt_insert->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_items->close();

        $mysqli->commit();
        return ['status' => 'success', 'message' => 'Order finalized successfully.'];

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        // Log the error in a real app
        return ['status' => 'error', 'message' => 'Failed to finalize order due to a database error.'];
    }
}
?>
