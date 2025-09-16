<?php
// public/index.php

// This will be the single entry point for the application.
// For now, it's just a placeholder.

echo "<h1>Welcome to the Marketplace!</h1>";
echo "<p>This is the main entry point.</p>";

// Basic routing logic will be added here later.
$request_uri = $_SERVER['REQUEST_URI'];

echo "<p>Request URI: " . htmlspecialchars($request_uri) . "</p>";
