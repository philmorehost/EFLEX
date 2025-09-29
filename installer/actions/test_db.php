<?php
// Start a session to store database credentials temporarily.
session_start();

// Set the content type to JSON for the response.
header('Content-Type: application/json');

// Check if the request method is POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get the database credentials from the POST data.
$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';

// Validate that required fields are not empty.
if (empty($db_host) || empty($db_name) || empty($db_user)) {
    echo json_encode(['status' => false, 'message' => 'Database host, name, and user are required.']);
    exit;
}

// Suppress errors to handle them manually.
mysqli_report(MYSQLI_REPORT_OFF);

// Attempt to connect to the database.
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for connection errors.
if ($conn->connect_error) {
    echo json_encode([
        'status' => false,
        'message' => 'Connection Failed: ' . $conn->connect_error
    ]);
} else {
    // If connection is successful, store the credentials in the session.
    $_SESSION['db_credentials'] = [
        'host' => $db_host,
        'name' => $db_name,
        'user' => $db_user,
        'pass' => $db_pass
    ];

    echo json_encode([
        'status' => true,
        'message' => 'Connection successful! You can now proceed to the next step.'
    ]);
    $conn->close();
}

exit;