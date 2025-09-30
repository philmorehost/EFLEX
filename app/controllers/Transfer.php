<?php
namespace Controllers;

use Core\Controller;

class Transfer extends Controller {
    private $userModel;
    private $settingModel;
    private $transactionModel;

    public function __construct() {
        // Ensure user is logged in
        if (!isLoggedIn()) {
            flash('auth_error', 'You must be logged in to access that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->settingModel = $this->model('Setting');
        $this->transactionModel = $this->model('Transaction');
    }

    /**
     * Renders the main transfer page and handles the transfer logic.
     */
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // --- Handle the transfer submission ---
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $transfer_fee = 30.00;

            $data = [
                'bank_code' => trim($_POST['bank_code']),
                'account_number' => trim($_POST['account_number']),
                'amount' => (float)trim($_POST['amount']),
                'narration' => trim($_POST['narration']) ?: 'Wallet Transfer',
                'user_id' => $_SESSION['user_id'],
                'error' => ''
            ];

            // --- Validation ---
            if (empty($data['bank_code']) || empty($data['account_number']) || empty($data['amount'])) {
                $data['error'] = 'Bank, account number, and amount are required.';
            } elseif ($data['amount'] < 100) {
                $data['error'] = 'Minimum transfer amount is ₦100.';
            } elseif (empty($_SESSION['enquiry_id']) || empty($_SESSION['verified_account_details']) ||
                      $_SESSION['verified_account_details']['account_number'] !== $data['account_number'] ||
                      $_SESSION['verified_account_details']['bank_code'] !== $data['bank_code']) {
                $data['error'] = 'Account details do not match the verified details. Please re-verify.';
            }

            $total_cost = $data['amount'] + $transfer_fee;

            if (!empty($data['error'])) {
                flash('service_error', $data['error'], 'alert alert-danger');
                header('Location: ' . BASE_URL . '/transfer');
                exit();
            }

            // --- Transfer Logic ---
            if (!$this->userModel->debitWallet($data['user_id'], $total_cost)) {
                flash('service_error', 'Insufficient wallet balance for amount plus fee.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/transfer');
                exit();
            }

            $transaction_id = $this->transactionModel->create([
                'user_id' => $data['user_id'],
                'service' => 'transfer',
                'ref' => 'VTU-TRF-' . time() . rand(100, 999),
                'amount' => $total_cost,
                'status' => 'pending',
                'description' => "Transfer of ₦{$data['amount']} to {$data['account_number']}"
            ]);

            if (!$transaction_id) {
                $this->userModel->creditWallet($data['user_id'], $total_cost);
                flash('service_error', 'A critical error occurred (TID:08). Please try again.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/transfer');
                exit();
            }

            $settings = $this->settingModel->getSettings();
            $payload = [
                'access_key' => $settings['payment_beewave_access_key'] ?? '',
                'secret_key' => $settings['payment_beewave_secret_key'] ?? '',
                'encrypt_key' => $settings['payment_beewave_encrypt_key'] ?? '',
                'enquiry_id' => $_SESSION['enquiry_id'],
                'account_number' => $data['account_number'],
                'bank_code' => $data['bank_code'],
                'amount' => $data['amount'],
                'narration' => $data['narration']
            ];

            $api_url = 'https://merchant.beewave.ng/api/v1/bank-transfer/local-transfer';
            $api_response = \Helpers\ApiService::post($api_url, $payload);
            $response_data = $api_response['data'] ?? [];

            if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === true) {
                $this->transactionModel->updateStatus($transaction_id, 'successful', json_encode($response_data));
                flash('service_success', 'Transfer was successful!');
            } else {
                $this->userModel->creditWallet($data['user_id'], $total_cost);
                $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode($response_data));
                $error_msg = $response_data['message'] ?? 'An unknown API error occurred.';
                flash('service_error', 'Transfer failed: ' . $error_msg, 'alert alert-danger');
            }

            // Clean up session variables
            unset($_SESSION['enquiry_id']);
            unset($_SESSION['verified_account_details']);

            header('Location: ' . BASE_URL . '/transfer');
            exit();

        } else {
            // --- Handle GET request to display the page ---
            $settings = $this->settingModel->getSettings();
            $api_key = $settings['payment_beewave_access_key'] ?? '';
            $banks = [];

            if (!empty($api_key)) {
                $api_url = "https://merchant.beewave.ng/api/v1/bank-transfer/banks?access_key={$api_key}";
                $api_response = \Helpers\ApiService::get($api_url);

                if ($api_response['success'] && isset($api_response['data']['status']) && $api_response['data']['status'] === true) {
                    $banks = $api_response['data']['banks'];
                } else {
                    flash('service_error', 'Could not retrieve the list of banks at this time.', 'alert alert-danger');
                }
            } else {
                flash('service_error', 'Bank transfer service is not configured by the administrator.', 'alert alert-danger');
            }

            $data = [
                'title' => 'Send Money',
                'description' => 'Transfer funds from your wallet to any Nigerian bank account.',
                'banks' => $banks
            ];
            $this->view('transfer/index', $data);
        }
    }

    /**
     * Handles AJAX request to verify a bank account number.
     */
    public function verifyAccount() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }

        $account_number = trim($_POST['account_number'] ?? '');
        $bank_code = trim($_POST['bank_code'] ?? '');

        if (empty($account_number) || empty($bank_code)) {
            echo json_encode(['status' => 'error', 'message' => 'Bank and account number are required.']);
            exit;
        }

        $settings = $this->settingModel->getSettings();
        $api_key = $settings['payment_beewave_access_key'] ?? '';
        $secret_key = $settings['payment_beewave_secret_key'] ?? '';

        if (empty($api_key) || empty($secret_key)) {
            echo json_encode(['status' => 'error', 'message' => 'Verification service is not configured.']);
            exit;
        }

        $api_url = 'https://merchant.beewave.ng/api/v1/bank-transfer/local-bank-verification';
        $payload = [
            'access_key' => $api_key,
            'secret_key' => $secret_key,
            'account_number' => $account_number,
            'bank_code' => $bank_code,
        ];

        $api_response = \Helpers\ApiService::post($api_url, $payload);
        $response_data = $api_response['data'] ?? [];

        if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === true) {
            // On success, store the enquiry_id in session for the final transfer
            $_SESSION['enquiry_id'] = $response_data['data']['enquiry_id'];
            $_SESSION['verified_account_details'] = [
                'account_number' => $account_number,
                'bank_code' => $bank_code
            ];

            echo json_encode(['status' => 'success', 'account_name' => $response_data['data']['acccount_name']]);
        } else {
            $error_msg = $response_data['message'] ?? 'Could not verify account number.';
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
        }
        exit;
    }
}