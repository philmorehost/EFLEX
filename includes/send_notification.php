<?php

function send_push_notification($player_ids, $message, $heading = "New Order!") {
    global $mysqli;

    // Fetch OneSignal settings from the database
    $settings_sql = "SELECT setting_key, setting_value FROM settings
                     WHERE setting_key IN ('onesignal_app_id', 'onesignal_rest_api_key')";
    $result = $mysqli->query($settings_sql);
    $settings = [];
    while($row = $result->fetch_assoc()){
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $app_id = $settings['onesignal_app_id'] ?? '';
    $rest_api_key = $settings['onesignal_rest_api_key'] ?? '';

    if (empty($app_id) || empty($rest_api_key) || empty($player_ids)) {
        error_log("OneSignal settings are not configured or no players to send to.");
        return false;
    }

    $fields = [
        'app_id' => $app_id,
        'include_player_ids' => $player_ids,
        'data' => ["foo" => "bar"],
        'headings' => ['en' => $heading],
        'contents' => ['en' => $message],
    ];

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $rest_api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
