<?php
// Function to redirect to a specific page
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Function to check if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get current user's data
function get_current_user() {
    global $mysqli;
    if (is_logged_in()) {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

// Function to check if the current user is an admin
function is_admin() {
    $user = get_current_user();
    return $user && $user['is_admin'] == 1;
}

// Function to protect pages that require login
function protect_page() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// Function to protect admin pages
function protect_admin_page() {
    if (!is_admin()) {
        redirect('../index.php');
    }
}

// Function to get a setting from the database
function get_setting($key) {
    global $mysqli;
    static $settings = []; // Use static cache to avoid multiple DB calls per request

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

// Function to update a setting in the database
function update_setting($key, $value) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param('sss', $key, $value, $value);
    return $stmt->execute();
}