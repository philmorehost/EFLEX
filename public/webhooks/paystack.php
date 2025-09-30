<?php
// public/webhooks/paystack.php - Handles incoming payment notifications from Paystack

// Only respond to POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit();
}

// Set header to acknowledge receipt
http_response_code(200);

// Bootstrap the application
require_once '../../config/bootstrap.php';

// --- Get Incoming Data and Signature ---
$json = file_get_contents('php://input');
$event = json_decode($json);

// --- Validate Data ---
if (!$event || !isset($event->event)) {
    error_log('Paystack Webhook: Invalid payload received.');
    exit();
}

// --- Verify Signature ---
$settingsModel = new \Models\Setting();
$settings = $settingsModel->getSettings();
$secret_key = $settings['payment_paystack_secret_key'] ?? '';

if (empty($secret_key)) {
    error_log('Paystack Webhook: Secret key is not configured.');
    exit();
}

$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$hash = hash_hmac('sha512', $json, $secret_key);

if ($hash !== $signature) {
    error_log('Paystack Webhook: Signature verification failed.');
    http_response_code(401); // Unauthorized
    exit();
}

// --- Process Event ---
if ($event->event === 'charge.success') {
    $data = $event->data;
    $transaction_ref = $data->reference;
    $amount_paid = (float)($data->amount / 100); // Amount is in kobo
    $customer_email = $data->customer->email;

    $db = new \Core\Database();

    try {
        // 1. Check if transaction has been processed
        $db->query('SELECT id FROM transactions WHERE transaction_ref = :ref');
        $db->bind(':ref', $transaction_ref);
        if ($db->rowCount() > 0) {
            error_log('Paystack Webhook: Duplicate transaction received for ref: ' . $transaction_ref);
            exit();
        }

        // 2. Find the user by email
        $db->query('SELECT id FROM users WHERE email = :email');
        $db->bind(':email', $customer_email);
        $user_row = $db->single();

        if (!$user_row) {
            throw new Exception('No user found for email: ' . $customer_email);
        }
        $user_id = $user_row->id;

        // 3. Update user's wallet
        $db->query('UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :user_id');
        $db->bind(':amount', $amount_paid);
        $db->bind(':user_id', $user_id);
        if (!$db->execute()) {
            throw new Exception('Failed to update wallet for user_id: ' . $user_id);
        }

        // 4. Log the transaction
        $db->query('INSERT INTO transactions (user_id, service, transaction_ref, amount, status, description, metadata)
                     VALUES (:user_id, :service, :ref, :amount, :status, :desc, :meta)');
        $db->bind(':user_id', $user_id);
        $db->bind(':service', 'deposit');
        $db->bind(':ref', $transaction_ref);
        $db->bind(':amount', $amount_paid);
        $db->bind(':status', 'successful');
        $db->bind(':desc', 'Wallet deposit via Paystack');
        $db->bind(':meta', $json);
        if (!$db->execute()) {
            throw new Exception('CRITICAL: Failed to log transaction after crediting wallet for user_id: ' . $user_id . ' ref: ' . $transaction_ref);
        }

        error_log('Paystack Webhook: Successfully processed deposit for user_id: ' . $user_id . ', amount: ' . $amount_paid);

    } catch (Exception $e) {
        error_log('Paystack Webhook Error: ' . $e->getMessage());
        http_response_code(500);
    }
}

exit();
?>