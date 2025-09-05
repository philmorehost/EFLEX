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

/**
 * Finalizes a successful order by updating its status and creating the relevant subscriptions.
 * This function is designed to be called after a payment has been successfully verified.
 *
 * @param int $order_id The ID of the order to finalize.
 * @param int $user_id The ID of the user who placed the order.
 * @return array An array with 'status' and optionally 'message'.
 */
function finalize_successful_order($order_id, $user_id) {
    global $mysqli;

    if (!$mysqli) {
        return ['status' => 'error', 'message' => 'Database connection failed.'];
    }

    $mysqli->begin_transaction();

    try {
        // Step 1: Update order status to 'Completed'
        $sql_update_order = "UPDATE orders SET status = 'Completed' WHERE id = ? AND user_id = ?";
        $stmt_update = $mysqli->prepare($sql_update_order);
        $stmt_update->bind_param("ii", $order_id, $user_id);
        if (!$stmt_update->execute() || $stmt_update->affected_rows === 0) {
            throw new Exception("Failed to update order status or order not found.");
        }
        $stmt_update->close();

        // Step 2: Get order items and their duration
        $sql_items = "SELECT oi.product_id, p.duration_days
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        $stmt_items = $mysqli->prepare($sql_items);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        $items = $result_items->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();

        if (empty($items)) {
            throw new Exception("No items found for this order.");
        }

        // Step 3: Create subscriptions for each item
        $sql_insert_sub = "INSERT INTO user_subscriptions (user_id, product_id, order_id, status, expires_at) VALUES (?, ?, ?, 'active', ?)";
        $stmt_insert = $mysqli->prepare($sql_insert_sub);

        foreach ($items as $item) {
            $product_id = $item['product_id'];
            $duration_days = (int)$item['duration_days'];

            // Calculate expiry date. If duration is 0 or less, it's a lifetime subscription (NULL expiry).
            $expires_at = $duration_days > 0 ? date('Y-m-d H:i:s', strtotime("+$duration_days days")) : null;

            $stmt_insert->bind_param("iiis", $user_id, $product_id, $order_id, $expires_at);
            $stmt_insert->execute();
        }
        $stmt_insert->close();

        $mysqli->commit();
        return ['status' => 'success'];

    } catch (Exception $e) {
        $mysqli->rollback();
        // In a real application, you'd log the error message $e->getMessage()
        return ['status' => 'error', 'message' => 'Failed to finalize order. Please contact support.'];
    }
}
?>
