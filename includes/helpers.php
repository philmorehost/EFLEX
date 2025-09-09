<?php
// Define a project root constant to make file includes robust.
if (!defined('PROJECT_ROOT')) {
    // Assumes helpers.php is in /includes/ at the project root.
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Manually include PHPMailer files using an absolute path
require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/Exception.php';
require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/PHPMailer.php';
require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/SMTP.php';

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

        // --- Fetch data for email notifications ---
        // Get user details
        $sql_user = "SELECT username, email FROM users WHERE id = ?";
        $stmt_user = $mysqli->prepare($sql_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();

        // Get order total
        $sql_order_total = "SELECT total_amount FROM orders WHERE id = ?";
        $stmt_order_total = $mysqli->prepare($sql_order_total);
        $stmt_order_total->bind_param("i", $order_id);
        $stmt_order_total->execute();
        $order_result = $stmt_order_total->get_result()->fetch_assoc();
        $stmt_order_total->close();

        // Re-fetch item names for the email
        $item_names = [];
        foreach ($items as $item) {
            $sql_product_name = "SELECT name FROM products WHERE id = ?";
            $stmt_product_name = $mysqli->prepare($sql_product_name);
            $stmt_product_name->bind_param("i", $item['product_id']);
            $stmt_product_name->execute();
            $product_result = $stmt_product_name->get_result()->fetch_assoc();
            $item_names[] = $product_result['name'];
            $stmt_product_name->close();
        }
        $mysqli->commit();

        // --- Send notification emails AFTER commit ---
        // Fetch data for email notifications outside of the transaction
        try {
            // Get user details
            $sql_user = "SELECT username, email FROM users WHERE id = ?";
            $stmt_user = $mysqli->prepare($sql_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $user_result = $stmt_user->get_result()->fetch_assoc();
            $stmt_user->close();

            // Get order total
            $sql_order_total = "SELECT total_amount FROM orders WHERE id = ?";
            $stmt_order_total = $mysqli->prepare($sql_order_total);
            $stmt_order_total->bind_param("i", $order_id);
            $stmt_order_total->execute();
            $order_result = $stmt_order_total->get_result()->fetch_assoc();
            $stmt_order_total->close();

            // Re-fetch item names for the email
            $item_names = [];
            foreach ($items as $item) {
                $sql_product_name = "SELECT name FROM products WHERE id = ?";
                $stmt_product_name = $mysqli->prepare($sql_product_name);
                $stmt_product_name->bind_param("i", $item['product_id']);
                $stmt_product_name->execute();
                $product_result = $stmt_product_name->get_result()->fetch_assoc();
                $item_names[] = $product_result['name'];
                $stmt_product_name->close();
            }
            $item_list_html = "<ul><li>" . implode("</li><li>", $item_names) . "</li></ul>";

            $site_title = get_app_setting('site_title', 'Eflex');

            // 1. Email to the user
            $user_subject = "Your Order Confirmation from " . $site_title;
            $user_body = "
                Hi " . htmlspecialchars($user_result['username']) . ",<br><br>
                Thank you for your order! Your payment has been confirmed and your subscription is now active.<br><br>
                <b>Order Details:</b><br>
                Order ID: #$order_id<br>
                Total Amount: " . format_price($order_result['total_amount']) . "<br>
                Items:<br>
                $item_list_html
                <br>
                You can view your active subscriptions in your account's 'Lesson Notes' section.<br><br>
                Best regards,<br>
                The " . $site_title . " Team
            ";
            send_notification_email($user_result['email'], $user_subject, $user_body);

            // 2. Email to the admin
            $admin_email = get_app_setting('admin_notification_email', get_app_setting('contact_email'));
            if(!empty($admin_email)) {
                $admin_subject = "New Order Notification (#$order_id) on " . $site_title;
                $admin_body = "
                    A new order has been placed and paid for on your website.<br><br>
                    <b>Order Details:</b><br>
                    Order ID: #$order_id<br>
                    Customer: " . htmlspecialchars($user_result['username']) . " (" . htmlspecialchars($user_result['email']) . ")<br>
                    Total Amount: " . format_price($order_result['total_amount']) . "<br>
                    Items:<br>
                    $item_list_html
                    <br>
                    The order has been marked as 'Completed' and the user's subscription has been activated automatically.
                ";
                send_notification_email($admin_email, $admin_subject, $admin_body);
            }
        } catch(Exception $e) {
            // Log email sending failure, but don't break the user flow
            // error_log("Failed to send order confirmation email for order ID $order_id: " . $e->getMessage());
        }

        return ['status' => 'success'];

    } catch (Exception $e) {
        $mysqli->rollback();
        // In a real application, you'd log the error message $e->getMessage()
        return ['status' => 'error', 'message' => 'Failed to finalize order. Please contact support.'];
    }
}


/**
 * Sends an email notification using PHPMailer with settings from the database.
 *
 * @param string $to The recipient's email address.
 * @param string $subject The email subject.
 * @param string $body The email body (HTML).
 * @param bool $is_html Whether the email body is HTML. Defaults to true.
 * @return bool True on success, false on failure.
 */
function send_notification_email($to, $subject, $body, $is_html = true) {
    $mail = new PHPMailer(true);

    try {
        // Server settings from the database
        $mail->isSMTP();
        $mail->Host       = get_app_setting('smtp_host');
        $mail->SMTPAuth   = true;
        $mail->Username   = get_app_setting('smtp_user');
        $mail->Password   = get_app_setting('smtp_pass');
        $mail->SMTPSecure = get_app_setting('smtp_encryption', PHPMailer::ENCRYPTION_SMTPS);
        $mail->Port       = get_app_setting('smtp_port', 465);

        // Recipients
        $mail->setFrom(get_app_setting('from_email'), get_app_setting('from_name', 'Eflex'));
        $mail->addAddress($to);

        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        if($is_html) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        // In a real app, you would log this error. For now, we just return false.
        // error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
