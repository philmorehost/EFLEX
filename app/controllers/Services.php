<?php
namespace Controllers;

use Core\Controller;

class Services extends Controller {
    private $userModel;
    private $settingModel;
    private $transactionModel;

    public function __construct() {
        // Ensure user is logged in to access any service
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
     * Renders the Airtime purchase page and handles the purchase logic.
     */
    public function airtime() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'network' => trim($_POST['network']),
                'phone_number' => trim($_POST['phone_number']),
                'amount' => (float)trim($_POST['amount']),
                'user_id' => $_SESSION['user_id'],
                'error' => ''
            ];

            // --- Validation ---
            if (empty($data['network']) || empty($data['phone_number']) || empty($data['amount'])) {
                $data['error'] = 'All fields are required.';
            } elseif ($data['amount'] <= 0) {
                $data['error'] = 'Please enter a valid amount.';
            }

            if (!empty($data['error'])) {
                flash('service_error', $data['error'], 'alert alert-danger');
                $this->view('services/airtime', ['title' => 'Buy Airtime', 'description' => 'Top up any mobile phone instantly.']);
                return;
            }

            // --- Purchase Logic ---
            // 1. Check for sufficient balance and debit wallet
            if (!$this->userModel->debitWallet($data['user_id'], $data['amount'])) {
                flash('service_error', 'Insufficient wallet balance.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/airtime');
                exit();
            }

            // 2. Log pending transaction
            $transaction_id = $this->transactionModel->create([
                'user_id' => $data['user_id'],
                'service' => 'airtime',
                'ref' => 'VTU-AIR-' . time() . rand(100, 999),
                'amount' => $data['amount'],
                'status' => 'pending',
                'description' => "Airtime purchase for {$data['phone_number']} on {$data['network']}"
            ]);

            if (!$transaction_id) {
                // Critical error: wallet debited but couldn't log. Re-credit user.
                $this->userModel->creditWallet($data['user_id'], $data['amount']);
                flash('service_error', 'A critical error occurred (TID:01). Please try again.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/airtime');
                exit();
            }

            // 3. Call the API
            $settings = $this->settingModel->getSettings();
            $api_key = $settings['vtu_datagifting_api_key'] ?? '';

            if (empty($api_key)) {
                 flash('service_error', 'Airtime service is currently unavailable. Please contact support.', 'alert alert-danger');
                 // Revert transaction
                 $this->userModel->creditWallet($data['user_id'], $data['amount']);
                 $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode(['error' => 'API key not configured']));
                 header('Location: ' . BASE_URL . '/services/airtime');
                 exit();
            }

            $api_url = 'https://v6.datagifting.com.ng/web/api/airtime.php';
            $payload = [
                'api_key' => $api_key,
                'network' => $data['network'],
                'phone_number' => $data['phone_number'],
                'amount' => $data['amount']
            ];

            $api_response = \Helpers\ApiService::post($api_url, $payload);
            $response_data = $api_response['data'] ?? [];

            // 4. Handle API response
            if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
                // Success! Update transaction
                $this->transactionModel->updateStatus($transaction_id, 'successful', json_encode($response_data));
                flash('service_success', 'Airtime purchase was successful!');
            } else {
                // Failed. Revert wallet and update transaction
                $this->userModel->creditWallet($data['user_id'], $data['amount']);
                $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode($response_data));
                $error_msg = $response_data['response_desc'] ?? 'An unknown API error occurred.';
                flash('service_error', 'Airtime purchase failed: ' . $error_msg, 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/services/airtime');
            exit();

        } else {
            // Display the form for GET request
            $data = [
                'title' => 'Buy Airtime',
                'description' => 'Top up any mobile phone instantly.'
            ];
            $this->view('services/airtime', $data);
        }
    }

    /**
     * Renders the Data purchase page and handles the purchase logic.
     */
    public function data() {
        // Storing plan details on the backend to prevent price tampering
        $allDataPlans = [
            'mtn' => [
                'sme-data' => [ '1gb' => 550, '2gb' => 1100, '3gb' => 1650 ],
                'cg-data' => [ '1gb' => 630, '2gb' => 1260 ]
            ],
            'glo' => [ 'cg-data' => [ '1gb' => 420, '2gb' => 840 ] ],
            'airtel' => [ 'cg-data' => [ '1gb' => 810, '2gb' => 1510 ] ],
            '9mobile' => [ 'cg-data' => [ '1gb' => 370, '2gb' => 740 ] ]
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'network' => trim($_POST['network']),
                'phone_number' => trim($_POST['phone_number']),
                'type' => trim($_POST['type']),
                'quantity' => trim($_POST['quantity']),
                'user_id' => $_SESSION['user_id'],
                'error' => ''
            ];

            // --- Validation & Price Check ---
            if (empty($data['network']) || empty($data['phone_number']) || empty($data['type']) || empty($data['quantity'])) {
                $data['error'] = 'All fields are required.';
            }

            $price = $allDataPlans[$data['network']][$data['type']][$data['quantity']] ?? 0;
            if ($price <= 0) {
                $data['error'] = 'Invalid data plan selected.';
            }

            if (!empty($data['error'])) {
                flash('service_error', $data['error'], 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/data');
                exit();
            }

            // --- Purchase Logic ---
            if (!$this->userModel->debitWallet($data['user_id'], $price)) {
                flash('service_error', 'Insufficient wallet balance.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/data');
                exit();
            }

            $transaction_id = $this->transactionModel->create([
                'user_id' => $data['user_id'],
                'service' => 'data',
                'ref' => 'VTU-DAT-' . time() . rand(100, 999),
                'amount' => $price,
                'status' => 'pending',
                'description' => "Data purchase: {$data['quantity']} of {$data['type']} for {$data['phone_number']}"
            ]);

            if (!$transaction_id) {
                $this->userModel->creditWallet($data['user_id'], $price);
                flash('service_error', 'A critical error occurred (TID:02). Please try again.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/data');
                exit();
            }

            $settings = $this->settingModel->getSettings();
            $api_key = $settings['vtu_datagifting_api_key'] ?? '';

            $api_url = 'https://v6.datagifting.com.ng/web/api/data.php';
            $payload = [
                'api_key' => $api_key,
                'network' => $data['network'],
                'phone_number' => $data['phone_number'],
                'type' => $data['type'],
                'quantity' => $data['quantity']
            ];

            $api_response = \Helpers\ApiService::post($api_url, $payload);
            $response_data = $api_response['data'] ?? [];

            if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
                $this->transactionModel->updateStatus($transaction_id, 'successful', json_encode($response_data));
                flash('service_success', 'Data purchase was successful!');
            } else {
                $this->userModel->creditWallet($data['user_id'], $price);
                $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode($response_data));
                $error_msg = $response_data['response_desc'] ?? 'An unknown API error occurred.';
                flash('service_error', 'Data purchase failed: ' . $error_msg, 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/services/data');
            exit();

        } else {
            $data = [
                'title' => 'Buy Data',
                'description' => 'Get instant data bundles for any network.'
            ];
            $this->view('services/data', $data);
        }
    }

    /**
     * Renders the Cable TV subscription page and handles the purchase.
     */
    public function cable() {
        $cablePackages = [
            'dstv' => [ 'padi' => 2150, 'yanga' => 2950, 'confam' => 5300, 'compact' => 9000 ],
            'gotv' => [ 'jolli' => 2800, 'max' => 4150 ],
            'startimes' => [ 'nova' => 900, 'basic' => 1700 ]
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'type' => trim($_POST['type']),
                'iuc_number' => trim($_POST['iuc_number']),
                'package' => trim($_POST['package']),
                'user_id' => $_SESSION['user_id'],
                'error' => ''
            ];

            // --- Validation & Price Check ---
            if (empty($data['type']) || empty($data['iuc_number']) || empty($data['package'])) {
                $data['error'] = 'All fields are required.';
            }

            $price = $cablePackages[$data['type']][$data['package']] ?? 0;
            if ($price <= 0) {
                $data['error'] = 'Invalid cable package selected.';
            }

            if (!empty($data['error'])) {
                flash('service_error', $data['error'], 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/cable');
                exit();
            }

            // --- Purchase Logic ---
            if (!$this->userModel->debitWallet($data['user_id'], $price)) {
                flash('service_error', 'Insufficient wallet balance.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/cable');
                exit();
            }

            $transaction_id = $this->transactionModel->create([
                'user_id' => $data['user_id'],
                'service' => 'cable',
                'ref' => 'VTU-CAB-' . time() . rand(100, 999),
                'amount' => $price,
                'status' => 'pending',
                'description' => "Cable TV subscription: {$data['package']} for {$data['iuc_number']}"
            ]);

            if (!$transaction_id) {
                $this->userModel->creditWallet($data['user_id'], $price);
                flash('service_error', 'A critical error occurred (TID:03). Please try again.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/cable');
                exit();
            }

            $settings = $this->settingModel->getSettings();
            $api_key = $settings['vtu_datagifting_api_key'] ?? '';

            $api_url = 'https://v6.datagifting.com.ng/web/api/cable.php';
            $payload = [
                'api_key' => $api_key,
                'type' => $data['type'],
                'iuc_number' => $data['iuc_number'],
                'package' => $data['package']
            ];

            $api_response = \Helpers\ApiService::post($api_url, $payload);
            $response_data = $api_response['data'] ?? [];

            if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
                $this->transactionModel->updateStatus($transaction_id, 'successful', json_encode($response_data));
                flash('service_success', 'Cable subscription was successful!');
            } else {
                $this->userModel->creditWallet($data['user_id'], $price);
                $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode($response_data));
                $error_msg = $response_data['response_desc'] ?? 'An unknown API error occurred.';
                flash('service_error', 'Cable subscription failed: ' . $error_msg, 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/services/cable');
            exit();

        } else {
            $data = [
                'title' => 'Cable TV Subscription',
                'description' => 'Renew your DSTV, GOtv, or Startimes subscription instantly.'
            ];
            $this->view('services/cable', $data);
        }
    }

    /**
     * Handles AJAX request to verify a cable card number.
     */
    public function verifyCable() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }

        $iuc_number = trim($_POST['iuc_number'] ?? '');
        $provider = trim($_POST['type'] ?? '');

        if (empty($iuc_number) || empty($provider)) {
            echo json_encode(['status' => 'error', 'message' => 'IUC number and provider are required.']);
            exit;
        }

        $settings = $this->settingModel->getSettings();
        $api_key = $settings['vtu_datagifting_api_key'] ?? '';

        $api_url = 'https://v6.datagifting.com.ng/web/api/verify-cable.php';
        $payload = [
            'api_key' => $api_key,
            'type' => $provider,
            'iuc_number' => $iuc_number,
        ];

        $api_response = \Helpers\ApiService::post($api_url, $payload);
        $response_data = $api_response['data'] ?? [];

        if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
            echo json_encode(['status' => 'success', 'customer_name' => $response_data['desc']]);
        } else {
            $error_msg = $response_data['desc'] ?? 'Could not verify IUC number.';
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
        }
        exit;
    }

    /**
     * Renders the Electricity Bill Payment page and handles the purchase.
     */
    public function electricity() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'provider' => trim($_POST['provider']),
                'meter_number' => trim($_POST['meter_number']),
                'type' => trim($_POST['type']),
                'amount' => (float)trim($_POST['amount']),
                'user_id' => $_SESSION['user_id'],
                'error' => ''
            ];

            // --- Validation ---
            if (empty($data['provider']) || empty($data['meter_number']) || empty($data['type']) || empty($data['amount'])) {
                $data['error'] = 'All fields are required.';
            } elseif ($data['amount'] < 100) {
                $data['error'] = 'Minimum purchase amount is ₦100.';
            }

            if (!empty($data['error'])) {
                flash('service_error', $data['error'], 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/electricity');
                exit();
            }

            // --- Purchase Logic ---
            if (!$this->userModel->debitWallet($data['user_id'], $data['amount'])) {
                flash('service_error', 'Insufficient wallet balance.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/electricity');
                exit();
            }

            $transaction_id = $this->transactionModel->create([
                'user_id' => $data['user_id'],
                'service' => 'electricity',
                'ref' => 'VTU-ELEC-' . time() . rand(100, 999),
                'amount' => $data['amount'],
                'status' => 'pending',
                'description' => "Electricity payment for meter {$data['meter_number']}"
            ]);

            if (!$transaction_id) {
                $this->userModel->creditWallet($data['user_id'], $data['amount']);
                flash('service_error', 'A critical error occurred (TID:04). Please try again.', 'alert alert-danger');
                header('Location: ' . BASE_URL . '/services/electricity');
                exit();
            }

            $settings = $this->settingModel->getSettings();
            $api_key = $settings['vtu_datagifting_api_key'] ?? '';

            $api_url = 'https://v6.datagifting.com.ng/web/api/electric.php';
            $payload = [
                'api_key' => $api_key,
                'provider' => $data['provider'],
                'meter_number' => $data['meter_number'],
                'type' => $data['type'],
                'amount' => $data['amount']
            ];

            $api_response = \Helpers\ApiService::post($api_url, $payload);
            $response_data = $api_response['data'] ?? [];

            if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
                $this->transactionModel->updateStatus($transaction_id, 'successful', json_encode($response_data));
                $success_message = $response_data['response_desc'] ?? 'Electricity payment was successful!';
                flash('service_success', $success_message);
            } else {
                $this->userModel->creditWallet($data['user_id'], $data['amount']);
                $this->transactionModel->updateStatus($transaction_id, 'failed', json_encode($response_data));
                $error_msg = $response_data['response_desc'] ?? 'An unknown API error occurred.';
                flash('service_error', 'Electricity payment failed: ' . $error_msg, 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/services/electricity');
            exit();

        } else {
            $data = [
                'title' => 'Electricity Bill Payment',
                'description' => 'Pay your prepaid or postpaid electricity bills instantly.'
            ];
            $this->view('services/electricity', $data);
        }
    }

    /**
     * Handles AJAX request to verify an electricity meter number.
     */
    public function verifyElectric() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }

        $meter_number = trim($_POST['meter_number'] ?? '');
        $provider = trim($_POST['provider'] ?? '');

        if (empty($meter_number) || empty($provider)) {
            echo json_encode(['status' => 'error', 'message' => 'Meter number and provider are required.']);
            exit;
        }

        $settings = $this->settingModel->getSettings();
        $api_key = $settings['vtu_datagifting_api_key'] ?? '';

        $api_url = 'https://v6.datagifting.com.ng/web/api/verify-electric.php';
        $payload = [
            'api_key' => $api_key,
            'provider' => $provider,
            'meter_number' => $meter_number,
        ];

        $api_response = \Helpers\ApiService::post($api_url, $payload);
        $response_data = $api_response['data'] ?? [];

        if ($api_response['success'] && isset($response_data['status']) && $response_data['status'] === 'success') {
            echo json_encode(['status' => 'success', 'customer_name' => $response_data['desc']]);
        } else {
            $error_msg = $response_data['desc'] ?? 'Could not verify meter number.';
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
        }
        exit;
    }
}