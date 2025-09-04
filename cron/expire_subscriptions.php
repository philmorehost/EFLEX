<?php
// This script is designed to be run by a cron job to manage subscription statuses.

// Set the base path to the project root
$base_path = __DIR__ . '/../';

// Include necessary files
require_once $base_path . 'includes/db_connect.php';
require_once $base_path . 'includes/helpers.php'; // For get_app_setting

// --- Main Logic ---

echo "Cron job started: " . date('Y-m-d H:i:s') . "\n";

// Find all active subscriptions where the expiry date is in the past
$sql = "SELECT us.id, u.email, u.username, p.name as product_name
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        JOIN products p ON us.product_id = p.id
        WHERE us.status = 'active'
        AND us.expires_at IS NOT NULL
        AND us.expires_at < NOW()";

$result = $mysqli->query($sql);
$expired_subscriptions = $result->fetch_all(MYSQLI_ASSOC);

if (count($expired_subscriptions) > 0) {
    echo "Found " . count($expired_subscriptions) . " expired subscriptions to process.\n";

    $sql_update = "UPDATE user_subscriptions SET status = 'expired' WHERE id = ?";
    $stmt_update = $mysqli->prepare($sql_update);

    foreach ($expired_subscriptions as $sub) {
        // Update the status in the database
        $stmt_update->bind_param("i", $sub['id']);
        $stmt_update->execute();

        echo "Subscription ID #" . $sub['id'] . " for user '" . $sub['username'] . "' has been marked as expired.\n";

        // --- Email Notification Logic (Placeholder) ---
        // In a real application, you would integrate a mailer library (like PHPMailer) here.
        // The mailer would be configured with the SMTP settings from the database.

        $user_email = $sub['email'];
        $subject = "Your Subscription has Expired";
        $body = "Hello " . $sub['username'] . ",\n\n";
        $body .= "This is a notification to let you know that your subscription to '" . $sub['product_name'] . "' has expired.\n";
        $body .= "To renew your subscription or purchase a new one, please visit our website.\n\n";
        $body .= "Thank you,\n";
        $body .= get_app_setting('site_title', 'Your Site');

        // Example of how you might call a mailer function:
        // send_email($user_email, $subject, $body);

        echo "-> Email notification queued for " . $user_email . "\n";
    }

    $stmt_update->close();
} else {
    echo "No expired subscriptions found.\n";
}

echo "Cron job finished: " . date('Y-m-d H:i:s') . "\n";

$mysqli->close();
?>
