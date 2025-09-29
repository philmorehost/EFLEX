<?php
// app/helpers/session_helper.php

// Ensure session is started in bootstrap.php, but check here just in case.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Flash message helper
 * EXAMPLE - In controller: flash('register_success', 'You are now registered');
 *           In view: echo flash('register_success');
 * @param string $name The name of the flash message (e.g., 'register_success')
 * @param string $message The message to store in the flash session
 * @param string $class The CSS class to apply to the message div (e.g., 'alert alert-success')
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        // If a message is passed, set the session
        if (!empty($message) && empty($_SESSION[$name])) {
            // Unset old session if it exists
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            // Set new session
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        }
        // If no message is passed, display the flash message
        elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            // Unset after displaying
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

/**
 * Checks if a user is logged in by looking for a session variable.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Creates a user session after successful login.
 * @param object $user The user object from the database.
 */
function createUserSession($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_username'] = $user->username;
    $_SESSION['user_role'] = $user->role;
}

/**
 * Destroys the user session on logout.
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_username']);
    unset($_SESSION['user_role']);
    session_destroy();
}