<?php
require_once __DIR__ . '/includes/config.php'; // Ensures session_start() is called

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to the login page with a success message
// Note: We can't set a session message here, so a GET parameter is a simple alternative.
header("Location: login.php?status=loggedout");
exit;
?>
