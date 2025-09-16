<?php
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    // Cannot redirect with header as we will be outputting a CSV
    die('Access Denied: Not logged in.');
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    die('Access Denied: Insufficient permissions.');
}

// --- Data Fetching ---
// This query joins all necessary tables and uses GROUP_CONCAT to flatten the options
// for each question into a single row, which is ideal for CSV export.
$sql = "SELECT
            qc.category_name,
            q.question_type,
            q.question_text,
            GROUP_CONCAT(o.option_text ORDER BY o.option_id SEPARATOR '|') as options,
            GROUP_CONCAT(o.is_correct ORDER BY o.option_id SEPARATOR '|') as are_correct
        FROM questions q
        LEFT JOIN question_categories qc ON q.category_id = qc.category_id
        LEFT JOIN options o ON q.question_id = o.question_id
        GROUP BY q.question_id
        ORDER BY q.question_id ASC";

$result = $conn->query($sql);

// --- CSV Generation ---
$filename = "questions_export_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, [
    'category_name',
    'question_type',
    'question_text',
    'options (pipe-separated)',
    'are_correct (pipe-separated, 1 for correct)'
]);

// Add data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
?>
