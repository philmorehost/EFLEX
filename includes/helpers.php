<?php
// A place for helper functions that can be used across the application.

// Global variable to cache settings so we don't query the DB repeatedly.
$app_settings = null;

function load_app_settings() {
    global $app_settings, $mysqli;

    // Only load settings if they haven't been loaded yet.
    if ($app_settings === null) {
        $app_settings = [];
        $sql = "SELECT setting_key, setting_value FROM settings";
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $app_settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

function get_app_setting($key, $default = '') {
    global $app_settings;

    // Ensure settings are loaded.
    if ($app_settings === null) {
        load_app_settings();
    }

    return $app_settings[$key] ?? $default;
}

function format_price($price) {
    $symbol = get_app_setting('currency_symbol', '$');
    // number_format adds commas and ensures two decimal places.
    return $symbol . number_format((float)$price, 2);
}
?>
