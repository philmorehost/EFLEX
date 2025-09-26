<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/init.php';

$manifest = [
    "name" => get_setting('pwa_name') ?: 'My Awesome App',
    "short_name" => get_setting('pwa_short_name') ?: 'MyApp',
    "start_url" => ".",
    "display" => "standalone",
    "background_color" => get_setting('pwa_background_color') ?: '#ffffff',
    "theme_color" => get_setting('pwa_theme_color') ?: '#000000',
    "description" => get_setting('site_description') ?: 'A description of your app.',
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

echo json_encode($manifest);
?>