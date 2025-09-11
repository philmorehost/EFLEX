<?php
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2]; // Only Super Admin and Admin can delete users
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: users.php?error=permissiondenied");
    exit;
}

// --- Get and Validate User ID ---
$user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id_to_delete) {
    header("Location: users.php?error=invalidid");
    exit;
}

// --- Security Checks ---
// Prevent user from deleting themselves
if ($user_id_to_delete === $_SESSION['user_id']) {
    header("Location: users.php?error=selfdelete");
    exit;
}

// Prevent deletion of the main Super Admin (user_id = 1)
if ($user_id_to_delete === 1) {
    header("Location: users.php?error=cantdeletesuperadmin");
    exit;
}

// --- Deletion Logic ---
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id_to_delete);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Deletion successful
        header("Location: users.php?success=userdeleted");
    } else {
        // User with that ID was not found
        header("Location: users.php?error=usernotfound");
    }
} else {
    // Database error
    header("Location: users.php?error=db_error");
}

$stmt->close();
exit;
?>
