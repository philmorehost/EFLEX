<?php
/**
 * Core Functions Library
 *
 * This file contains essential functions used throughout the application.
 * Each function is wrapped in a `function_exists()` check as a safeguard
 * to prevent fatal "Cannot redeclare function" errors.
 */

if (!function_exists('redirect')) {
    /**
     * Redirects the user to a specific URL and terminates the script.
     * @param string $url The URL to redirect to.
     */
    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Checks if a user is currently logged in by looking for a user ID in the session.
     * @return bool True if the user is logged in, false otherwise.
     */
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('get_current_user')) {
    /**
     * Fetches the current user's data from the database.
     * Uses a static variable to cache the result for the duration of the request.
     * @return array Guaranteed to return an array. Empty if not logged in or user not found.
     */
    function get_current_user() {
        global $mysqli;

        // Use a uniquely named static variable to cache user data for the request.
        static $current_user_cache = null;

        // Only query if the cache hasn't been populated yet.
        if ($current_user_cache === null) {
            // Default to an empty array.
            $current_user_cache = [];

            if (is_logged_in()) {
                // Explicitly select the columns needed to avoid issues with `SELECT *`.
                $stmt = $mysqli->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_data = $result->fetch_assoc();
                    $stmt->close();

                    if (is_array($user_data)) {
                        $current_user_cache = $user_data;
                    }
                }
            }
        }

        return $current_user_cache;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Checks if the currently logged-in user is an administrator.
     * @return bool True if the user is an admin, false otherwise.
     */
    function is_admin() {
        $user = get_current_user();
        return !empty($user['is_admin']);
    }
}

if (!function_exists('protect_page')) {
    /**
     * Protects a page from access by non-logged-in users.
     * Redirects to the login page if the user is not authenticated.
     */
    function protect_page() {
        if (!is_logged_in()) {
            redirect('login.php');
        }
    }
}

if (!function_exists('protect_admin_page')) {
    /**
     * Protects a page from access by non-administrators.
     * Redirects to the site's homepage if the user is not an admin.
     */
    function protect_admin_page() {
        if (!is_admin()) {
            redirect('../index.php');
        }
    }
}

if (!function_exists('get_setting')) {
    /**
     * Retrieves a specific setting from the database.
     * Uses a static cache to prevent multiple database calls for the same setting.
     * @param string $key The name of the setting to retrieve.
     * @return string The value of the setting, or an empty string if not found.
     */
    function get_setting($key) {
        global $mysqli;
        static $settings = [];

        if (isset($settings[$key])) {
            return $settings[$key];
        }

        $stmt = $mysqli->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $settings[$key] = $row['setting_value'];
            return $row['setting_value'];
        }
        return '';
    }
}

if (!function_exists('update_setting')) {
    /**
     * Adds a new setting or updates an existing one in the database.
     * @param string $key The name of the setting.
     * @param string $value The value to set.
     * @return bool True on success, false on failure.
     */
    function update_setting($key, $value) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('sss', $key, $value, $value);
        return $stmt->execute();
    }
}
?>