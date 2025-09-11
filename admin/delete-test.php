<?php
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: tests.php?error=permissiondenied");
    exit;
}

// --- Get and Validate Test ID ---
$test_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$test_id_to_delete) {
    header("Location: tests.php?error=invalidid");
    exit;
}

// --- Deletion Logic ---
// The 'test_questions' table has ON DELETE CASCADE, so associated links will be deleted automatically.
$stmt = $conn->prepare("DELETE FROM tests WHERE test_id = ?");
$stmt->bind_param("i", $test_id_to_delete);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Deletion successful
        header("Location: tests.php?success=test_deleted");
    } else {
        // Test with that ID was not found
        header("Location: tests.php?error=notfound");
    }
} else {
    // Database error
    header("Location: tests.php?error=db_error");
}

$stmt->close();
exit;
?>
