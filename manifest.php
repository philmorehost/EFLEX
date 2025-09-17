<?php
require_once __DIR__ . '/includes/config.php';

// Fetch all settings from the database
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// --- Build the Manifest Array ---
// Use settings from DB or provide defaults
$manifest = [
    'short_name' => $settings['pwa_short_name'] ?? 'CBT App',
    'name' => $settings['pwa_name'] ?? 'CBT Platform',
    'description' => $settings['pwa_description'] ?? 'A web-based platform for computer-based testing.',
    'icons' => [],
    'start_url' => '/index.php',
    'display' => 'standalone',
    'theme_color' => '#007bff',
    'background_color' => '#ffffff',
    'scope' => '/',
    'orientation' => 'portrait-primary'
];

// Add icons if they are set in the settings
if (!empty($settings['pwa_icon_192'])) {
    $manifest['icons'][] = [
        'src' => $settings['pwa_icon_192'],
        'type' => 'image/png',
        'sizes' => '192x192',
        'purpose' => 'any maskable'
    ];
}
if (!empty($settings['pwa_icon_512'])) {
    $manifest['icons'][] = [
        'src' => $settings['pwa_icon_512'],
        'type' => 'image/png',
        'sizes' => '512x512'
    ];
}

// --- Output the Manifest ---
header('Content-Type: application/json');
echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
