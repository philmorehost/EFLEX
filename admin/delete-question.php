<?php
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: questions.php?error=permissiondenied");
    exit;
}

// --- Get and Validate Question ID ---
$question_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$question_id_to_delete) {
    header("Location: questions.php?error=invalidid");
    exit;
}

// --- Deletion Logic ---
// The 'options' table has ON DELETE CASCADE, so associated options will be deleted automatically.
$stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
$stmt->bind_param("i", $question_id_to_delete);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: questions.php?success=q_deleted");
    } else {
        // Question with that ID was not found
        header("Location: questions.php?error=q_not_found");
    }
} else {
    // Database error
    header("Location: questions.php?error=db_error");
}

$stmt->close();
exit;
?>
