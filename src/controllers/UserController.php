<?php
// src/controllers/UserController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../lib/Session.php';

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Only handle POST requests
            http_response_code(405); // Method Not Allowed
            echo 'Invalid request method.';
            return;
        }

        // 1. Get and sanitize input
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
        $password = $_POST['password']; // We won't sanitize this, just hash it
        $userType = $_POST['user_type'] === 'seller' ? 'seller' : 'buyer';

        // 2. Validate input
        if (!$username || !$email || empty($password)) {
            // Simple validation: check if fields are empty or email is invalid
            // In a real app, you'd handle this more gracefully (e.g., redirect with error message)
            die('Validation failed: Please fill all fields correctly.');
        }

        // 3. Check if user already exists
        if ($this->userModel->findByEmail($email)) {
            die('Error: An account with this email already exists.');
        }

        // 4. Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 5. Create the user
        $success = $this->userModel->create($username, $email, $passwordHash, $userType);

        // 6. Redirect on success
        if ($success) {
            // Redirect to login page with a success message
            Session::flash('success_message', 'Registration successful! Please log in.');
            header('Location: /login');
            exit();
        } else {
            // Handle failure
            Session::flash('error_message', 'An unexpected error occurred. Please try again.');
            header('Location: /register');
            exit();
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Invalid request method.';
            return;
        }

        // 1. Get and validate input
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email || empty($password)) {
            Session::flash('error_message', 'Invalid email or password.');
            header('Location: /login');
            exit();
        }

        // 2. Find user by email
        $user = $this->userModel->findByEmail($email);

        // 3. Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, set session
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('user_type', $user['user_type']);

            // Redirect to homepage or dashboard
            header('Location: /');
            exit();
        } else {
            // Invalid credentials
            Session::flash('error_message', 'Invalid email or password.');
            header('Location: /login');
            exit();
        }
    }

    public function logout() {
        Session::destroy();
        header('Location: /');
        exit();
    }
}
