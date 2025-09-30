<?php
namespace Controllers;

use Core\Controller;

class Admin extends Controller {

    public function __construct() {
        // Ensure user is logged in
        if (!isLoggedIn()) {
            flash('auth_error', 'You must be logged in to view that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }

        // Ensure user is an admin
        if ($_SESSION['user_role'] !== 'admin') {
            flash('auth_error', 'You do not have permission to view that page.', 'alert alert-danger');
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }

        // Load models that will be used by all methods in this controller
        $this->settingModel = $this->model('Setting');
        $this->loanModel = $this->model('Loan');
        $this->userModel = $this->model('User');
        $this->landingPageModel = $this->model('LandingPage');
    }

    public function index() {
        // Default admin page, could be a dashboard overview
        // For now, redirect to settings
        header('Location: ' . BASE_URL . '/admin/settings');
        exit();
    }

    public function loans() {
        $pending_loans = $this->loanModel->getAllPendingLoans();
        $data = [
            'title' => 'Manage Loan Applications',
            'pending_loans' => $pending_loans
        ];
        $this->view('admin/loans', $data);
    }

    public function approveLoan($loan_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->loanModel->approve($loan_id)) {
                flash('loan_management_success', 'Loan has been approved and funds disbursed.');
            } else {
                flash('loan_management_error', 'Failed to approve loan.', 'alert alert-danger');
            }
        }
        header('Location: ' . BASE_URL . '/admin/loans');
        exit();
    }

    public function denyLoan($loan_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->loanModel->deny($loan_id)) {
                flash('loan_management_success', 'Loan application has been denied.');
            } else {
                flash('loan_management_error', 'Failed to deny loan application.', 'alert alert-danger');
            }
        }
        header('Location: ' . BASE_URL . '/admin/loans');
        exit();
    }

    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Loop through all posted settings and update them
            $update_success = true;
            foreach ($_POST as $key => $value) {
                if (!$this->settingModel->updateSetting($key, trim($value))) {
                    $update_success = false;
                }
            }

            if ($update_success) {
                flash('settings_success', 'Settings have been updated successfully.');
            } else {
                flash('settings_success', 'Could not update all settings.', 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/admin/settings');
            exit();

        } else {
            // Load the view with existing settings on GET request
            $settings = $this->settingModel->getSettings();

            $data = [
                'title' => 'Admin Settings',
                'description' => 'Manage your application API keys and other settings.',
                'settings' => $settings
            ];

            $this->view('admin/settings', $data);
        }
    }

    public function landingPage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data, but allow some basic HTML in textareas
            $post_data = [];
            foreach($_POST as $key => $value) {
                $post_data[$key] = trim($value); // Basic trim, more specific sanitization could be added
            }

            $update_success = true;
            foreach ($post_data as $key => $value) {
                if (!$this->landingPageModel->updateContent($key, $value)) {
                    $update_success = false;
                }
            }

            if ($update_success) {
                flash('content_success', 'Landing page content has been updated successfully.');
            } else {
                flash('content_error', 'Could not update all content fields.', 'alert alert-danger');
            }

            header('Location: ' . BASE_URL . '/admin/landingPage');
            exit();

        } else {
            // Load the view with existing content on GET request
            $content = $this->landingPageModel->getContent();

            $data = [
                'title' => 'Manage Landing Page Content',
                'description' => 'Update the text and images displayed on your homepage.',
                'content' => $content
            ];

            $this->view('admin/landing_page', $data);
        }
    }
}