<?php
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    die('Access Denied: Not logged in.');
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    die('Access Denied: Insufficient permissions.');
}

// --- Data Fetching ---
// Select only students (role_id = 4)
$sql = "SELECT first_name, last_name, email FROM users WHERE role_id = 4 ORDER BY user_id ASC";
$result = $conn->query($sql);

// --- CSV Generation ---
$filename = "students_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, ['first_name', 'last_name', 'email']);

// Add data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
?>
