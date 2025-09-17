<?php
// src/lib/FlutterwaveClient.php

class FlutterwaveClient {
    // In a real app, the API key would be passed to the constructor
    public function __construct($apiKey) {
        // ...
    }

    /**
     * Simulate initializing a transaction.
     * In a real app, this would make an API call to Flutterwave.
     * @param string $email
     * @param float $amount
     * @param string $tx_ref
     * @return array A dummy response.
     */
    public function initializeTransaction($email, $amount, $tx_ref) {
        // Simulate a successful API call
        return [
            'status' => 'success',
            'message' => 'Hosted Link',
            'data' => [
                'link' => '/mock/payment/confirm?gateway=flutterwave&ref=' . $tx_ref
            ]
        ];
    }

    /**
     * Simulate verifying a transaction.
     * In a real app, this would make an API call to Flutterwave.
     * @param string $transaction_id
     * @return array A dummy response.
     */
    public function verifyTransaction($transaction_id) {
        // Simulate a successful verification
        return [
            'status' => 'success',
            'message' => 'Transaction fetched successfully',
            'data' => [
                'status' => 'successful',
                'tx_ref' => 'mock_ref_' . $transaction_id,
                'amount' => 50.00
            ]
        ];
    }
}
