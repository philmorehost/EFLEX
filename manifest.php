<?php
// This file dynamically generates the manifest.json file for the PWA.

require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get PWA settings from the database
$pwa_name = get_app_setting('pwa_name', get_app_setting('site_title', 'Eflex'));
$pwa_short_name = get_app_setting('pwa_short_name', get_app_setting('site_title', 'Eflex'));
$pwa_theme_color = get_app_setting('pwa_theme_color', '#ffffff');
$pwa_background_color = get_app_setting('pwa_background_color', '#ffffff');

// Define the icons. The URLs should be absolute paths from the root.
$icon_192_url = get_app_setting('pwa_icon_192_url', '/icon-192x192.png');
$icon_512_url = get_app_setting('pwa_icon_512_url', '/icon-512x512.png');

// Construct the manifest data structure
$manifest = [
    'name' => $pwa_name,
    'short_name' => $pwa_short_name,
    'start_url' => '/',
    'display' => 'standalone',
    'background_color' => $pwa_background_color,
    'theme_color' => $pwa_theme_color,
    'icons' => [
        [
            'src' => $icon_192_url,
            'sizes' => '192x192',
            'type' => 'image/png'
        ],
        [
            'src' => $icon_512_url,
            'sizes' => '512x512',
            'type' => 'image/png'
        ]
    ]
];

// Output the manifest as a JSON string
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
