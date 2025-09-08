<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/google_drive_api.php';

// Security check: ensure user is a logged-in admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    http_response_code(403);
    die('Access Denied');
}

// Check if the authorization code is present
if (!isset($_GET['code'])) {
    die('Error: Authorization code not found.');
}

$auth_code = $_GET['code'];

// Get the stored client ID and secret
$credentials = get_google_drive_credentials();
if (isset($credentials['error']) || empty($credentials['client_id']) || empty($credentials['client_secret'])) {
    die('Google Drive API credentials are not configured. Please set them up first.');
}

$client_id = $credentials['client_id'];
$client_secret = $credentials['client_secret'];
$redirect_uri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/admin/gdrive_oauth_callback.php';

// --- Exchange authorization code for tokens ---
$token_url = 'https://oauth2.googleapis.com/token';
$post_data = [
    'code' => $auth_code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die('cURL Error (exchanging code for token): ' . curl_error($ch));
}
curl_close($ch);

$token_data = json_decode($response, true);

if (isset($token_data['error'])) {
    die('Google API Error (exchanging code for token): ' . ($token_data['error_description'] ?? json_encode($token_data['error'])));
}

// --- Save the new credentials (including the refresh token) ---
if (isset($token_data['refresh_token'])) {
    $new_creds = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $token_data['refresh_token']
    ];

    $credentials_file = __DIR__ . '/secure/gdrive.dat';
    if (file_put_contents($credentials_file, json_encode($new_creds, JSON_PRETTY_PRINT))) {
        // Success! Redirect back to the settings page.
        header('Location: manage_drive.php');
        exit();
    } else {
        die('Failed to save credentials to file. Please check file permissions for <code>/admin/secure/gdrive.dat</code>.');
    }
} else {
    // This can happen on re-authorization if you don't use prompt=consent
    die('Error: Refresh token not received from Google. Please try re-authorizing from the Manage Drive page. Make sure you are granting offline access.');
}
?>
