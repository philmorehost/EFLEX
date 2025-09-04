<?php

function get_google_drive_credentials() {
    $credential_path = __DIR__ . '/../admin/secure/gdrive.dat';
    if (!file_exists($credential_path)) {
        return ['error' => 'Credential file not found.'];
    }
    $credentials = json_decode(file_get_contents($credential_path), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Credential file is not valid JSON.'];
    }
    return $credentials;
}

function get_google_drive_access_token() {
    $credentials = get_google_drive_credentials();
    if (isset($credentials['error'])) {
        return $credentials; // Pass the error up
    }

    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'client_id' => $credentials['client_id'],
        'client_secret' => $credentials['client_secret'],
        'refresh_token' => $credentials['refresh_token'],
        'grant_type' => 'refresh_token'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = 'cURL Error (getting access token): ' . curl_error($ch);
        curl_close($ch);
        return ['error' => $error_msg];
    }
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['error'])) {
        return ['error' => 'Google API Error (getting access token): ' . ($token_data['error_description'] ?? json_encode($token_data['error']))];
    }

    if (isset($token_data['access_token'])) {
        return $token_data['access_token'];
    }

    return ['error' => 'Unknown error getting access token.'];
}

function list_google_drive_files($folder_id = 'root') {
    $access_token_response = get_google_drive_access_token();
    if (is_array($access_token_response) && isset($access_token_response['error'])) {
        return ['error' => ['message' => 'Failed to get access token: ' . $access_token_response['error']]];
    }
    $access_token = $access_token_response;

    $query = "'$folder_id' in parents and trashed = false";
    $api_url = 'https://www.googleapis.com/drive/v3/files?q=' . urlencode($query) . '&fields=files(id,name,mimeType,webViewLink)&orderBy=folder,name';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = 'cURL Error (listing files): ' . curl_error($ch);
        curl_close($ch);
        return ['error' => ['message' => $error_msg]];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    return $data;
}

function set_file_uncoppyable($file_id) {
    $access_token_response = get_google_drive_access_token();
    if (is_array($access_token_response) && isset($access_token_response['error'])) {
        return ['error' => ['message' => 'Failed to get access token for setting permissions: ' . $access_token_response['error']]];
    }
    $access_token = $access_token_response;

    $api_url = 'https://www.googleapis.com/drive/v3/files/' . $file_id . '?supportsAllDrives=true';
    $post_data = json_encode(['copyRequiresWriterPermission' => true]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token, 'Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function get_file_details($file_id) {
    // First, ensure the file is not copyable
    set_file_uncoppyable($file_id);

    $access_token_response = get_google_drive_access_token();
    if (is_array($access_token_response) && isset($access_token_response['error'])) {
        return ['error' => ['message' => 'Failed to get access token for file details: ' . $access_token_response['error']]];
    }
    $access_token = $access_token_response;

    $api_url = 'https://www.googleapis.com/drive/v3/files/' . $file_id . '?fields=id,name,webViewLink';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
?>
