<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/includes/config.php';

// --- Auth Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// --- Handle Profile Picture Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // 1. Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "An error occurred during file upload. Please try again.";
    } else {
        // 2. Validate file size (e.g., max 2MB)
        $max_size = 2 * 1024 * 1024; // 2 MB
        if ($file['size'] > $max_size) {
            $errors[] = "File is too large. Maximum size is 2MB.";
        }

        // 3. Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }

        // 4. If validation passes, process the file
        if (empty($errors)) {
            $upload_dir = __DIR__ . '/uploads/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // 5. Update database with the new path
                $relative_path = 'uploads/profile_pictures/' . $new_filename;

                $stmt = $conn->prepare("UPDATE users SET profile_picture_path = ? WHERE user_id = ?");
                $stmt->bind_param("si", $relative_path, $user_id);

                if ($stmt->execute()) {
                    $success_message = "Profile picture updated successfully!";
                } else {
                    $errors[] = "Database error: Could not save profile picture path.";
                }
                $stmt->close();
            } else {
                $errors[] = "Could not move uploaded file. Check server permissions.";
            }
        }
    }
}

// --- Fetch user data (including the new profile pic path) ---
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$profile_pic = $user['profile_picture_path'] ?? 'assets/img/default_avatar.svg';
if (empty($user['profile_picture_path']) || !file_exists(__DIR__ . '/' . $user['profile_picture_path'])) {
    $profile_pic = 'assets/img/default_avatar.svg';
}


require_once __DIR__ . '/includes/header.php';

// Include the correct navbar based on user role
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4) { // Student
    // I need to create the student_navbar.php file in a later step
    // For now, let's assume it exists for the purpose of the profile page
    // include __DIR__ . '/includes/student_navbar.php';
} elseif (isset($_SESSION['role_id'])) { // Admin/Other roles
    // Assuming a generic or admin navbar exists
    // include __DIR__ . '/includes/admin_navbar.php';
}

?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>My Profile</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>

                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

                    <hr>

                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Change Profile Picture</label>
                            <input class="form-control" type="file" id="profile_picture" name="profile_picture" required>
                            <div class="form-text">Max file size: 2MB. Allowed types: JPG, PNG, GIF.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Picture</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
