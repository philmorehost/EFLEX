<?php
// src/lib/PaystackClient.php

class PaystackClient {
    // In a real app, the API key would be passed to the constructor
    public function __construct($apiKey) {
        // ...
    }

    /**
     * Simulate initializing a transaction.
     * In a real app, this would make an API call to Paystack.
     * @param string $email
     * @param float $amount
     * @param string $reference
     * @return array A dummy response.
     */
    public function initializeTransaction($email, $amount, $reference) {
        // Simulate a successful API call
        return [
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => '/mock/payment/confirm?gateway=paystack&ref=' . $reference,
                'access_code' => 'mock_access_code_' . uniqid(),
                'reference' => $reference
            ]
        ];
    }

    /**
     * Simulate verifying a transaction.
     * In a real app, this would make an API call to Paystack.
     * @param string $reference
     * @return array A dummy response.
     */
    public function verifyTransaction($reference) {
        // Simulate a successful verification
        return [
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'status' => 'success',
                'reference' => $reference,
                'amount' => 500000 // amount in kobo (e.g., 5000 NGN)
            ]
        ];
    }
}
