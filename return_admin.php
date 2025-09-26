<?php
// This script handles restoring the admin's session after impersonating a user.
session_start();

// The 'functions.php' file is needed for the redirect() function.
require_once __DIR__ . '/includes/functions.php';

// Check if an admin_id is stored in the session, which indicates impersonation.
if (isset($_SESSION['admin_id'])) {
    // Restore the admin's original user ID.
    $_SESSION['user_id'] = $_SESSION['admin_id'];

    // Remove the temporary admin_id from the session.
    unset($_SESSION['admin_id']);

    // Redirect the admin back to their dashboard.
    redirect('admin/index.php');
} else {
    // If a non-admin user somehow lands here, send them to their own dashboard.
    redirect('dashboard.php');
}
?>