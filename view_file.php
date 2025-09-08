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
// Admins have access to all files
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $has_access = true;
} else {
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
}

// --- Stream or Display File if Access is Granted ---
if ($has_access) {
    $file_path = __DIR__ . '/' . $file_info['filepath'];
    if (file_exists($file_path)) {
        $mimetype = $file_info['mimetype'];

        // For images and text, embed in a secure HTML viewer
        if (strpos($mimetype, 'image/') === 0 || $mimetype === 'text/plain') {
            include 'includes/header.php'; // Display the website header

            echo '<div class="container my-5">';
            echo '<h3>Viewing: ' . htmlspecialchars($file_info['original_filename']) . '</h3>';
            echo '<hr>';

            // Secure content wrapper to disable printing/copying
            echo '<div class="secure-content">';

            $content = file_get_contents($file_path);
            if (strpos($mimetype, 'image/') === 0) {
                // Embed image using a data URI
                echo '<img src="data:' . $mimetype . ';base64,' . base64_encode($content) . '" class="img-fluid" alt="' . htmlspecialchars($file_info['original_filename']) . '">';
            } elseif ($mimetype === 'text/plain') {
                // Display plain text in a preformatted block
                echo '<pre>' . htmlspecialchars($content) . '</pre>';
            }

            echo '</div>'; // end .secure-content
            echo '</div>'; // end .container

            include 'includes/footer.php'; // Display the website footer
            exit;
        } else {
            // For other file types (PDF, etc.), stream directly
            header('Content-Type: ' . $mimetype);
            header('Content-Disposition: inline; filename="' . basename($file_info['original_filename']) . '"');
            header('Content-Length: ' . filesize($file_path));
            ob_clean();
            flush();
            readfile($file_path);
            exit;
        }
    } else {
        http_response_code(404);
        die("File not found on server.");
    }
} else {
    http_response_code(403);
    die("Access Denied: You do not have an active subscription for this item.");
}
?>
