<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'A valid email is required.';
        redirect('../forgot-password.php');
    }

    // --- Check if the user exists ---
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // To prevent user enumeration, we'll show the same message whether the user exists or not.
    if ($user) {
        // --- Generate a secure token ---
        $token = bin2hex(random_bytes(32));

        // --- Set an expiration time (e.g., 1 hour from now) ---
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // --- Store the token in the database ---
        $stmt = $mysqli->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $email, $token, $expires_at);
        $stmt->execute();
        $stmt->close();

        // --- In a real application, you would send an email here. ---
        // For demonstration purposes in this environment, we will display the link directly.
        $reset_link = "http://localhost:8000/reset-password.php?token=" . $token;
        $_SESSION['reset_link'] = $reset_link;
    }

    // --- Show a success message to the user ---
    $_SESSION['success'] = 'If an account with that email exists, a password reset link has been generated.';
    redirect('../forgot-password.php');
} else {
    // Redirect non-POST requests back to the homepage.
    redirect('../index.php');
}
?>