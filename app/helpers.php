<?php
// app/helpers.php

/**
 * Simulates sending a notification to administrators by writing to a log file.
 *
 * @param string $message The notification message.
 * @return void
 */
/**
 * Sends an email using the system's configured SMTP settings.
 *
 * @param PDO $pdo The database connection object.
 * @param string $to The recipient's email address.
 * @param string $subject The email subject.
 * @param string $message The email message body (HTML).
 * @return bool True on success, false on failure.
 */
function send_email(PDO $pdo, string $to, string $subject, string $message): bool {
    try {
        $settings_raw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $settings = array_merge([
            'smtp_host' => 'localhost',
            'smtp_port' => '25',
            'smtp_user' => '',
            'smtp_pass' => '',
        ], $settings_raw);

        // This is a basic implementation and may not work on all systems.
        // A library like PHPMailer is recommended for production.
        ini_set('SMTP', $settings['smtp_host']);
        ini_set('smtp_port', $settings['smtp_port']);
        ini_set('sendmail_from', 'no-reply@cbt-platform.com');

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <no-reply@cbt-platform.com>' . "\r\n";

        // The mail function is notoriously unreliable and is stubbed here.
        // In a real environment, this would attempt to send an email.
        // We will simulate success by logging the email instead.
        $log_message = "--- EMAIL SENT ---\nTO: $to\nSUBJECT: $subject\nMESSAGE: " . strip_tags($message) . "\n------------------\n";
        file_put_contents(ROOT_PATH . '/logs/sent_emails.log', $log_message, FILE_APPEND);

        return true; // Simulate success
        // return mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Notifies all administrators of a critical event.
 *
 * @param PDO $pdo The database connection object.
 * @param string $message The notification message.
 * @return void
 */
function notify_admins(PDO $pdo, string $message): void {
    try {
        $sql = "SELECT email FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name IN ('Super Admin', 'Admin')";
        $admin_emails = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

        $subject = "CBT Platform Admin Notification";

        foreach ($admin_emails as $email) {
            // We are calling the send_email function we created.
            // In our sandboxed environment, this will log the email instead of sending it.
            send_email($pdo, $email, $subject, $message);
        }
    } catch (Exception $e) {
        // If notifications fail, we log it to the main error log.
        error_log("Failed to send admin notifications: " . $e->getMessage());
    }
}
?>
