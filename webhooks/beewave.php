<?php
// public/webhooks/beewave.php - Handles incoming payment notifications from Beewave

// Set header to indicate a successful response to the sender
http_response_code(200);

// Bootstrap the application to access the database and models
require_once '../config/bootstrap.php';

// --- Get Incoming Data ---
$json = file_get_contents('php://input');
$data = json_decode($json);

// --- Validate Data ---
if (!$data || !isset($data->status) || !isset($data->data->transaction_ref)) {
    // If data is invalid or missing key fields, log it and exit.
    error_log('Beewave Webhook: Invalid or empty payload received.');
    exit();
}

// Check if it's a successful transaction notification
if ($data->status !== true || $data->data->status !== 'success') {
    // Not a successful payment, no need to process further.
    error_log('Beewave Webhook: Received non-successful transaction status for ref: ' . ($data->data->transaction_ref ?? 'N/A'));
    exit();
}

// --- Process Transaction ---
$db = new \Core\Database(); // Manually instantiate since we're outside the MVC flow
$transaction_ref = $data->data->transaction_ref;
$amount_paid = (float)$data->data->amount_paid;
$customer_tracking_ref = $data->data->customer->tracking_ref;

try {
    // 1. Check if this transaction has already been processed
    $db->query('SELECT id FROM transactions WHERE transaction_ref = :ref');
    $db->bind(':ref', $transaction_ref);
    if ($db->rowCount() > 0) {
        error_log('Beewave Webhook: Duplicate transaction received for ref: ' . $transaction_ref);
        exit(); // Exit silently, we've already processed this.
    }

    // 2. Find the user associated with the virtual account
    $db->query('SELECT user_id FROM virtual_accounts WHERE tracking_ref = :tracking_ref');
    $db->bind(':tracking_ref', $customer_tracking_ref);
    $user_row = $db->single();

    if (!$user_row) {
        throw new Exception('No user found for tracking_ref: ' . $customer_tracking_ref);
    }
    $user_id = $user_row->user_id;

    // 3. Start a database transaction
    // Note: The Database class needs to be extended to support transactions for this to work.
    // For now, we'll proceed with individual queries and add transaction support later if needed.

    // 4. Update user's wallet balance
    $db->query('UPDATE users SET wallet_balance = wallet_balance + :amount WHERE id = :user_id');
    $db->bind(':amount', $amount_paid);
    $db->bind(':user_id', $user_id);
    if (!$db->execute()) {
        throw new Exception('Failed to update wallet for user_id: ' . $user_id);
    }

    // 5. Log the transaction
    $db->query('INSERT INTO transactions (user_id, service, transaction_ref, amount, status, description, metadata)
                 VALUES (:user_id, :service, :ref, :amount, :status, :desc, :meta)');
    $db->bind(':user_id', $user_id);
    $db->bind(':service', 'deposit');
    $db->bind(':ref', $transaction_ref);
    $db->bind(':amount', $amount_paid);
    $db->bind(':status', 'successful');
    $db->bind(':desc', 'Wallet deposit via Beewave Virtual Account');
    $db->bind(':meta', $json); // Store the full webhook payload
    if (!$db->execute()) {
        // If logging fails, we have a problem. The user was credited but there's no record.
        // This is where transactions are crucial. We'll log a critical error.
        throw new Exception('CRITICAL: Failed to log transaction after crediting wallet for user_id: ' . $user_id . ' and ref: ' . $transaction_ref);
    }

    // If we get here, everything was successful.
    error_log('Beewave Webhook: Successfully processed deposit for user_id: ' . $user_id . ', amount: ' . $amount_paid);

} catch (Exception $e) {
    // Log any errors that occur during processing.
    error_log('Beewave Webhook Error: ' . $e->getMessage());
    // Optionally, send an alert to an admin.
    http_response_code(500); // Respond with an error status to the webhook sender.
}

exit();
?>