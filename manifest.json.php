<?php
header('Content-Type: application/json');
// The header template includes the core files we need.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Construct the manifest array, pulling values from the database via get_setting().
// Provide sensible defaults in case the settings are not yet configured.
$manifest = [
    "name" => get_setting('pwa_name') ?: 'My Marketplace',
    "short_name" => get_setting('pwa_short_name') ?: 'Market',
    "start_url" => ".",
    "display" => "standalone",
    "background_color" => get_setting('pwa_background_color') ?: '#ffffff',
    "theme_color" => get_setting('pwa_theme_color') ?: '#000000',
    "description" => get_setting('site_description') ?: 'A marketplace for digital goods.',
    "icons" => [
        [
            "src" => "assets/images/icons/icon-192x192.png",
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => "assets/images/icons/icon-512x512.png",
            "sizes" => "512x512",
            "type" => "image/png"
        ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>