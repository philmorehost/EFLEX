<?php
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get User ID and Validate ---
$user_id_to_deny = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id_to_deny) {
    header("Location: users.php?error=invalidid");
    exit;
}

// --- Deny/Delete User Logic ---
$conn->begin_transaction();

try {
    // 1. Get the profile picture path before deleting the user
    $stmt_select = $conn->prepare("SELECT profile_picture_path FROM users WHERE user_id = ?");
    $stmt_select->bind_param("i", $user_id_to_deny);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $user = $result->fetch_assoc();
    $profile_pic_path = $user['profile_picture_path'] ?? null;
    $stmt_select->close();

    // 2. Delete the user from the database
    $stmt_delete = $conn->prepare("DELETE FROM users WHERE user_id = ? AND status = 'pending'");
    $stmt_delete->bind_param("i", $user_id_to_deny);
    $stmt_delete->execute();

    $affected_rows = $stmt_delete->affected_rows;
    $stmt_delete->close();

    if ($affected_rows > 0) {
        // 3. If user was deleted, delete their profile picture file
        if ($profile_pic_path && file_exists(__DIR__ . '/../' . $profile_pic_path)) {
            unlink(__DIR__ . '/../' . $profile_pic_path);
        }
        $conn->commit();
        header("Location: users.php?success=userdenied");
    } else {
        // User was not pending or not found
        $conn->rollback();
        header("Location: users.php?error=notpending");
    }
} catch (Exception $e) {
    $conn->rollback();
    // In a real app, you'd log this error
    header("Location: users.php?error=dberror");
}
exit;
?>
