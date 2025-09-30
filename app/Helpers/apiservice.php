<?php
namespace Helpers;

/**
 * ApiService Class
 * A helper class to simplify making cURL requests to external APIs.
 */
class ApiService {

    /**
     * Makes a POST request to a given URL.
     *
     * @param string $url The URL to send the request to.
     * @param array $data The data to send in the request body.
     * @param array $headers An array of HTTP headers.
     * @return array The decoded JSON response and HTTP status code.
     */
    public static function post($url, $data = [], $headers = ['Content-Type: application/json']) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30-second timeout

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            // In a real app, log the error
            return ['success' => false, 'message' => 'API request failed: ' . $error, 'http_code' => $http_code];
        }

        $decoded_response = json_decode($response, true);

        // Check if json_decode failed
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Failed to decode API response.', 'raw_response' => $response, 'http_code' => $http_code];
        }

        return ['success' => true, 'data' => $decoded_response, 'http_code' => $http_code];
    }

    /**
     * Makes a GET request to a given URL.
     *
     * @param string $url The URL to send the request to.
     * @param array $headers An array of HTTP headers.
     * @return array The decoded JSON response and HTTP status code.
     */
    public static function get($url, $headers = []) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'API request failed: ' . $error, 'http_code' => $http_code];
        }

        $decoded_response = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Failed to decode API response.', 'raw_response' => $response, 'http_code' => $http_code];
        }

        return ['success' => true, 'data' => $decoded_response, 'http_code' => $http_code];
    }
}