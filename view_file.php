<?php
// Initialize session and check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    die("Access Denied: You must be logged in.");
}

// Include necessary files
require_once 'includes/db_connect.php';

// --- Input Validation ---
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($file_id <= 0) {
    http_response_code(400);
    die("Invalid file request.");
}

$user_id = $_SESSION['id'];

// --- Get File and Product Info ---
$sql_file = "SELECT product_id, filepath, mimetype, original_filename FROM product_local_files WHERE id = ?";
$stmt_file = $mysqli->prepare($sql_file);
$stmt_file->bind_param("i", $file_id);
$stmt_file->execute();
$result_file = $stmt_file->get_result();
$file_info = $result_file->fetch_assoc();
$stmt_file->close();

if (!$file_info) {
    http_response_code(404);
    die("File not found.");
}

$product_id = $file_info['product_id'];

// --- Check User Subscription ---
$has_access = false;
$sql_sub = "SELECT id FROM user_subscriptions
            WHERE user_id = ?
            AND product_id = ?
            AND status = 'active'
            AND (expires_at IS NULL OR expires_at >= CURDATE())";
$stmt_sub = $mysqli->prepare($sql_sub);
$stmt_sub->bind_param("ii", $user_id, $product_id);
$stmt_sub->execute();
$stmt_sub->store_result();
if ($stmt_sub->num_rows > 0) {
    $has_access = true;
}
$stmt_sub->close();

// --- Stream File if Access is Granted ---
if ($has_access) {
    $file_path = __DIR__ . '/' . $file_info['filepath'];
    if (file_exists($file_path)) {
        header('Content-Type: ' . $file_info['mimetype']);
        // Use 'inline' to suggest viewing in browser, 'attachment' to force download
        header('Content-Disposition: inline; filename="' . $file_info['original_filename'] . '"');
        header('Content-Length: ' . filesize($file_path));

        // Clear output buffer
        ob_clean();
        flush();

        readfile($file_path);
        exit;
    } else {
        http_response_code(404);
        die("File not found on server.");
    }
} else {
    http_response_code(403);
    die("Access Denied: You do not have an active subscription for this item.");
}
?>
