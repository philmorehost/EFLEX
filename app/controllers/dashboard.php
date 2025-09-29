<?php
namespace Controllers;

use Core\Controller;

class Dashboard extends Controller {
    public function __construct() {
        // If user is not logged in, redirect to login page.
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '/users/login');
            exit();
        }
    }

    public function index() {
        // This is the main dashboard page for logged-in users.
        $data = [
            'title' => 'Dashboard',
            'description' => 'Welcome to your dashboard, ' . $_SESSION['user_username'] . '!',
            'balance' => 'Fetching balance...' // Placeholder for wallet balance
        ];

        $this->view('dashboard/index', $data);
    }
}