<?php
namespace Controllers;

use Core\Controller;

class Wallet extends Controller {
    private $virtualAccountModel;
    private $userModel;
    private $settingModel;

    public function __construct() {
        // Ensure user is logged in before they can access wallet features
        if (!isLoggedIn()) {
            flash('auth_error', 'You must be logged in to access that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }

        $this->virtualAccountModel = $this->model('VirtualAccount');
        $this->userModel = $this->model('User');
        $this->settingModel = $this->model('Setting');
    }

    /**
     * Main wallet page where users can see funding options.
     * This method also handles the generation of a virtual account if one doesn't exist.
     */
    public function index() {
        $user_id = $_SESSION['user_id'];
        $virtual_account = $this->virtualAccountModel->getAccountByUserId($user_id);

        if (!$virtual_account) {
            // No account exists, so let's try to create one.
            $settings = $this->settingModel->getSettings();
            $user = $this->userModel->getUserById($user_id);

            $beewave_access_key = $settings['payment_beewave_access_key'] ?? null;

            if (empty($beewave_access_key) || empty($user)) {
                // Pre-requisites not met.
                flash('wallet_error', 'The site administrator has not configured payment settings correctly. Cannot generate a funding account.', 'alert alert-danger');
            } else {
                // All good, let's call the Beewave API
                $api_url = 'https://merchant.beewave.ng/api/v1/bank-transfer/virtual-account-numbers';
                $payload = [
                    'access_key' => $beewave_access_key,
                    'bank_code' => ['100039'], // Using Paystack Titan Microfinance as per docs
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    // bvn, nin, etc. are optional and not collected for now
                ];

                $api_response = \Helpers\ApiService::post($api_url, $payload);

                if ($api_response['success'] && isset($api_response['data']['status']) && $api_response['data']['status'] === true) {
                    $new_account = $api_response['data']['virtual_accounts'][0];

                    $account_data = [
                        'user_id' => $user_id,
                        'tracking_ref' => $new_account['tracking_ref'],
                        'account_number' => $new_account['account_number'],
                        'account_name' => $new_account['account_name'],
                        'bank_name' => $new_account['bank_name'],
                        'bank_code' => $new_account['bank_code']
                    ];

                    if ($this->virtualAccountModel->createVirtualAccount($account_data)) {
                        // Success! Set the virtual_account for the view
                        $virtual_account = (object)$account_data;
                    } else {
                        flash('wallet_error', 'API call was successful, but we failed to save your new account. Please contact support.', 'alert alert-danger');
                    }
                } else {
                    // API call failed
                    $error_message = $api_response['data']['message'] ?? 'An unknown error occurred while generating your funding account.';
                    flash('wallet_error', 'API Error: ' . $error_message, 'alert alert-danger');
                }
            }
        }

        $settings = $this->settingModel->getSettings();
        $user = $this->userModel->getUserById($user_id);

        $data = [
            'title' => 'Fund Your Wallet',
            'description' => 'Choose one of the methods below to add money to your account.',
            'virtual_account' => $virtual_account,
            'paystack_public_key' => $settings['payment_paystack_public_key'] ?? '',
            'flutterwave_public_key' => $settings['payment_flutterwave_public_key'] ?? '',
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'user_name' => $user->first_name . ' ' . $user->last_name,
        ];

        $this->view('wallet/index', $data);
    }
}