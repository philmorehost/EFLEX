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

$credentials = get_google_drive_credentials();
if (isset($credentials['error']) || empty($credentials['client_id'])) {
    die('Google Drive API credentials are not configured. Please set them up in the Manage Drive page.');
}

$client_id = $credentials['client_id'];

// The redirect URI must be authorized in your Google Cloud Console project.
$redirect_uri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/admin/gdrive_oauth_callback.php';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/drive', // This scope gives full access. You could restrict it if needed.
    'access_type' => 'offline', // Required to get a refresh token
    'prompt' => 'consent' // Forces the consent screen to be shown, which is needed to get a refresh token on re-authorization.
]);

// Redirect the user to the Google authorization page
header('Location: ' . $auth_url);
exit();
?>
