<?php
namespace Controllers;

use Core\Controller;

class Savings extends Controller {
    private $userModel;
    private $savingsModel;

    public function __construct() {
        if (!isLoggedIn()) {
            flash('auth_error', 'You must be logged in to access that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->savingsModel = $this->model('Savings');
    }

    /**
     * Renders the main savings page and handles transfers to savings.
     */
    public function index() {
        $user_id = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $amount = (float)trim($_POST['amount']);

            if (empty($amount) || $amount <= 0) {
                flash('savings_error', 'Please enter a valid amount.', 'alert alert-danger');
            } else {
                // Attempt to move funds
                if ($this->savingsModel->moveToSavings($user_id, $amount)) {
                    flash('savings_success', 'Successfully transferred ₦' . number_format($amount, 2) . ' to your savings vault.');
                } else {
                    flash('savings_error', 'Transfer failed. You may have insufficient funds in your main wallet.', 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . '/savings');
            exit();
        }

        // For GET request, display the page
        $user = $this->userModel->getUserById($user_id);
        $savings_account = $this->savingsModel->getSavingsByUserId($user_id);

        $data = [
            'title' => 'My Savings Vault',
            'description' => 'Save money for the future. Transfer funds from your main wallet to your savings vault.',
            'wallet_balance' => $user->wallet_balance,
            'savings_balance' => $savings_account ? $savings_account->balance : 0
        ];
        $this->view('savings/index', $data);
    }
}