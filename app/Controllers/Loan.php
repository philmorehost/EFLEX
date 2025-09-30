<?php
namespace Controllers;

use Core\Controller;

class Loan extends Controller {
    private $userModel;
    private $loanModel;
    private $transactionModel;

    public function __construct() {
        if (!isLoggedIn()) {
            flash('auth_error', 'You must be logged in to access that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->loanModel = $this->model('Loan');
        $this->transactionModel = $this->model('Transaction');
    }

    /**
     * Renders the main loans page, showing loan status and application form.
     */
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle loan application submission
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $amount = (float)trim($_POST['amount']);

            if (empty($amount) || $amount <= 0) {
                flash('loan_error', 'Please enter a valid loan amount.', 'alert alert-danger');
            } else {
                // Check eligibility and apply
                $result = $this->loanModel->applyForLoan($_SESSION['user_id'], $amount);
                if ($result['success']) {
                    flash('loan_success', 'Your loan application has been submitted successfully and is pending review.');
                } else {
                    flash('loan_error', $result['message'], 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . '/loan');
            exit();

        } else {
            // Display the page
            $user_id = $_SESSION['user_id'];
            $active_loan = $this->loanModel->getActiveLoanByUserId($user_id);
            $eligibility = $this->loanModel->checkEligibility($user_id);

            $data = [
                'title' => 'Apply for a Loan',
                'description' => 'Get a quick loan to fund your wallet.',
                'active_loan' => $active_loan,
                'is_eligible' => $eligibility['is_eligible'],
                'max_loan_amount' => $eligibility['max_loan_amount'],
                'eligibility_message' => $eligibility['message']
            ];
            $this->view('loan/index', $data);
        }
    }

    public function repay($loan_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $amount = (float)trim($_POST['amount']);

            if (empty($amount) || $amount <= 0) {
                flash('loan_error', 'Please enter a valid repayment amount.', 'alert alert-danger');
            } else {
                $result = $this->loanModel->makeRepayment($loan_id, $_SESSION['user_id'], $amount);
                if ($result['success']) {
                    flash('loan_success', 'Repayment of ₦' . number_format($amount, 2) . ' was successful.');
                } else {
                    flash('loan_error', $result['message'], 'alert alert-danger');
                }
            }
        }
        header('Location: ' . BASE_URL . '/loan');
        exit();
    }
}