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
$user_id_to_approve = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id_to_approve) {
    header("Location: users.php?error=invalidid");
    exit;
}

// --- Update User Status ---
$stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id_to_approve);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Optionally, send an email notification to the user here
        header("Location: users.php?success=userapproved");
    } else {
        header("Location: users.php?error=notpending"); // User was not pending or not found
    }
} else {
    header("Location: users.php?error=dberror");
}
$stmt->close();
exit;
?>
