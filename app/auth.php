<?php
// app/auth.php

/**
 * A helper function to be called at the top of protected pages.
 * It checks if a user is logged in and has the required role,
 * and handles displaying an access denied message if they do not.
 *
 * @param array $allowed_roles An array of role names that are allowed to access the page.
 */
function enforce_access(array $allowed_roles) {
    // Session must be started before calling this function.
    if (!isset($_SESSION['user_id'])) {
        // This case should ideally be caught by header.php, but it's a good fallback.
        header('Location: login.php');
        exit();
    }

    $user_role = $_SESSION['user_role'] ?? '';

    if (!in_array($user_role, $allowed_roles)) {
        // The user is logged in but does not have the required role.
        // We can't redirect, as that might cause a loop.
        // Instead, we display an error message within the existing page layout.
        echo '<div class="container-fluid"><div class="alert alert-danger mt-4"><strong>Access Denied:</strong> You do not have the required permissions to view this page.</div></div>';

        // We need to include the footer to ensure the page renders correctly.
        // This path assumes the calling file is in a subdirectory like /public/admin/
        require_once __DIR__ . '/../../templates/footer.php';
        exit(); // Stop executing the rest of the page.
    }
}
?>
