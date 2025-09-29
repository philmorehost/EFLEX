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
    }

    public function index() {
        // Default admin page, could be a dashboard overview
        // For now, redirect to settings
        header('Location: ' . BASE_URL . '/admin/settings');
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
}