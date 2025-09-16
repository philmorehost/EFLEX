<?php
// src/lib/Session.php

class Session {
    /**
     * Set a session variable.
     * @param string $key The key of the session variable.
     * @param mixed $value The value to store.
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable.
     * @param string $key The key of the session variable.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value of the session variable or the default value.
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session variable is set.
     * @param string $key The key of the session variable.
     * @return bool True if the key exists, false otherwise.
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable.
     * @param string $key The key of the session variable to remove.
     */
    public static function remove($key) {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the entire session.
     */
    public static function destroy() {
        // Unset all session variables
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Get and/or set a flash message.
     * A flash message is a session variable that is removed after being read once.
     * @param string $key The key for the flash message.
     * @param string $message The message to store. If empty, it will try to retrieve the message.
     * @return string|null The flash message, or null if not found.
     */
    public static function flash($key, $message = '') {
        if (!empty($message)) {
            // Set the flash message
            self::set('flash_' . $key, $message);
        } else {
            // Get the flash message
            $message = self::get('flash_' . $key);
            if ($message) {
                self::remove('flash_' . $key);
                return $message;
            }
        }
        return null;
    }
}
