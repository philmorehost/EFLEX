<?php
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get and Validate Category ID ---
$category_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$category_id_to_delete) {
    header("Location: question-categories.php?error=invalidid");
    exit;
}

// --- Deletion Logic ---
// The 'questions' table has ON DELETE CASCADE for category_id,
// so deleting a category will also delete all questions within it.
// And the 'options' table has ON DELETE CASCADE for question_id,
// so options will be deleted as well. This is a significant cascade.
$stmt = $conn->prepare("DELETE FROM question_categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id_to_delete);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: question-categories.php?success=cat_deleted");
    } else {
        header("Location: question-categories.php?error=cat_not_found");
    }
} else {
    header("Location: question-categories.php?error=db_error");
}

$stmt->close();
exit;
?>
