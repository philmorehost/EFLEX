<?php
// This script is designed to be run by a cron job to handle expired subscriptions.

// Set a default timezone to avoid potential date/time issues.
date_default_timezone_set('UTC');

// The script needs to be able to find the config and helper files.
// We assume it's in a 'cron' directory at the project root.
$project_root = dirname(__DIR__);
require_once $project_root . '/includes/db_connect.php';
require_once $project_root . '/includes/helpers.php';

echo "Cron Job: Expire Subscriptions - Started at " . date('Y-m-d H:i:s') . "\n";

// Find all subscriptions that are currently active but have expired.
// We also fetch user and product info needed for notifications.
$sql = "SELECT
            us.id as subscription_id,
            us.expires_at,
            u.id as user_id,
            u.username,
            u.email,
            p.name as product_name
        FROM
            user_subscriptions us
        JOIN
            users u ON us.user_id = u.id
        JOIN
            products p ON us.product_id = p.id
        WHERE
            us.status = 'active'
            AND us.expires_at IS NOT NULL
            AND us.expires_at < NOW()";

$result = $mysqli->query($sql);

if ($result === false) {
    echo "Error executing query: " . $mysqli->error . "\n";
    exit;
}

$expired_subscriptions = $result->fetch_all(MYSQLI_ASSOC);
$expired_count = count($expired_subscriptions);

if ($expired_count === 0) {
    echo "No active subscriptions have expired. Job finished.\n";
    exit;
}

echo "Found " . $expired_count . " expired subscriptions. Processing...\n";

$processed_for_admin_email = [];

// Prepare the statement to update the status
$sql_update = "UPDATE user_subscriptions SET status = 'expired' WHERE id = ?";
$stmt_update = $mysqli->prepare($sql_update);

foreach ($expired_subscriptions as $sub) {
    // --- 1. Update the subscription status in the database ---
    $stmt_update->bind_param("i", $sub['subscription_id']);
    if ($stmt_update->execute()) {
        echo "  - Expired subscription #" . $sub['subscription_id'] . " for user '" . $sub['username'] . "' for product '" . $sub['product_name'] . "'.\n";

        // --- 2. Send notification email to the user ---
        $site_title = get_app_setting('site_title', 'Eflex');
        $user_subject = "Your Subscription to " . htmlspecialchars($sub['product_name']) . " has expired";
        $user_body = "
            Hi " . htmlspecialchars($sub['username']) . ",<br><br>
            This is a notification to let you know that your subscription to the class '<b>" . htmlspecialchars($sub['product_name']) . "</b>' has expired as of " . date("M j, Y", strtotime($sub['expires_at'])) . ".<br><br>
            You will no longer be able to access the content for this class. To regain access, you will need to purchase a new subscription.<br><br>
            Thank you for being a customer.<br><br>
            Best regards,<br>
            The " . $site_title . " Team
        ";

        if (send_notification_email($sub['email'], $user_subject, $user_body)) {
            echo "    - Sent expiration notice to " . $sub['email'] . ".\n";
        } else {
            echo "    - FAILED to send expiration notice to " . $sub['email'] . ".\n";
        }

        // Add to the list for the admin summary email
        $processed_for_admin_email[] = $sub;

    } else {
        echo "  - FAILED to expire subscription #" . $sub['subscription_id'] . ". Error: " . $stmt_update->error . "\n";
    }
}

$stmt_update->close();

// --- 3. Send a summary email to the admin ---
if (!empty($processed_for_admin_email)) {
    $admin_email = get_app_setting('admin_notification_email', get_app_setting('contact_email'));
    if (!empty($admin_email)) {
        $site_title = get_app_setting('site_title', 'Eflex');
        $admin_subject = "Subscription Expiration Cron Job Summary - " . date('Y-m-d');

        $admin_body = "The daily subscription expiration script has completed. Here is the summary:<br><br>";
        $admin_body .= "<b>Total Subscriptions Expired:</b> " . count($processed_for_admin_email) . "<br><br>";
        $admin_body .= "<table border='1' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th>Subscription ID</th>
                                <th>User</th>
                                <th>Product</th>
                                <th>Expired On</th>
                            </tr>";

        foreach ($processed_for_admin_email as $processed_sub) {
            $admin_body .= "<tr>
                                <td>#" . $processed_sub['subscription_id'] . "</td>
                                <td>" . htmlspecialchars($processed_sub['username']) . " (" . htmlspecialchars($processed_sub['email']) . ")</td>
                                <td>" . htmlspecialchars($processed_sub['product_name']) . "</td>
                                <td>" . date("M j, Y", strtotime($processed_sub['expires_at'])) . "</td>
                            </tr>";
        }

        $admin_body .= "</table><br>User notifications have been sent automatically.";

        if (send_notification_email($admin_email, $admin_subject, $admin_body)) {
            echo "Admin summary email sent successfully.\n";
        } else {
            echo "FAILED to send admin summary email.\n";
        }
    }
}

echo "Cron Job: Expire Subscriptions - Finished at " . date('Y-m-d H:i:s') . "\n";

$mysqli->close();
