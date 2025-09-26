<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

// Check if an admin is impersonating a user
if (isset($_SESSION['admin_id'])) {
    // Restore the admin's session
    $_SESSION['user_id'] = $_SESSION['admin_id'];
    unset($_SESSION['admin_id']);

    // Redirect to the admin dashboard
    redirect('admin/index.php');
} else {
    // If not impersonating, just go to the regular dashboard
    redirect('dashboard.php');
}
?>