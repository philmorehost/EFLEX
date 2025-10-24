<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // --- Basic validation ---
    if (empty($token) || empty($password) || empty($password_confirm)) {
        $_SESSION['error'] = 'All fields are required.';
        redirect("../reset-password.php?token=$token");
    }
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        redirect("../reset-password.php?token=$token");
    }
    if ($password !== $password_confirm) {
        $_SESSION['error'] = 'Passwords do not match.';
        redirect("../reset-password.php?token=$token");
    }

    // --- Look up the token in the database ---
    $stmt = $mysqli->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset_request = $result->fetch_assoc();
    $stmt->close();

    if (!$reset_request) {
        $_SESSION['error'] = 'Invalid or expired password reset link.';
        redirect('../forgot-password.php');
    }

    // --- Check if the token has expired ---
    if (strtotime($reset_request['expires_at']) < time()) {
        $_SESSION['error'] = 'Invalid or expired password reset link.';
        // Clean up the expired token.
        $stmt = $mysqli->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();
        redirect('../forgot-password.php');
    }

    // --- All checks passed, update the password ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param('ss', $hashed_password, $reset_request['email']);

    if ($stmt->execute()) {
        // --- Invalidate the token by deleting it ---
        $stmt_delete = $mysqli->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt_delete->bind_param('s', $token);
        $stmt_delete->execute();
        $stmt_delete->close();

        $_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
        redirect('../login.php');
    } else {
        $_SESSION['error'] = 'Failed to reset your password. Please try again.';
        redirect("../reset-password.php?token=$token");
    }
    $stmt->close();

} else {
    // Redirect non-POST requests.
    redirect('../index.php');
}
?>