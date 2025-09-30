<?php
namespace Controllers;

use Core\Controller;

class Dashboard extends Controller {
    private $userModel;

    public function __construct() {
        // If user is not logged in, redirect to login page.
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }
        $this->userModel = $this->model('User');
    }

    public function index() {
        // Get fresh user data from the database
        $user = $this->userModel->getUserById($_SESSION['user_id']);

        // This is the main dashboard page for logged-in users.
        $data = [
            'title' => 'Dashboard',
            'description' => 'Welcome to your dashboard, ' . $user->username . '!',
            'balance' => '₦' . number_format($user->wallet_balance, 2)
        ];

        $this->view('dashboard/index', $data);
    }
}