<?php
// Check if the config file exists. If not, redirect to the installer.
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: install/');
    exit;
}

$pageTitle = "Register";
require_once __DIR__ . '/includes/config.php'; // Use config for db connection

$errors = [];
$success_message = '';

// Process form only when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $profile_picture = $_FILES['profile_picture'] ?? null;

    // --- Validation ---
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($address) || empty($phone) || empty($sex)) {
        $errors[] = "All fields are required.";
    }
    if ($profile_picture === null || $profile_picture['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Profile picture is required and must be uploaded successfully.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // --- Profile Picture Validation ---
    $profile_picture_path = null;
    if (empty($errors) && $profile_picture) {
        $upload_dir = 'uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($profile_picture['type'], $allowed_types)) {
            $errors[] = "Invalid file type for profile picture. Please upload a JPG, PNG, or GIF.";
        } else {
            $file_extension = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid('user_', true) . '.' . $file_extension;
            $profile_picture_path = $upload_dir . $unique_filename;
            if (!move_uploaded_file($profile_picture['tmp_name'], $profile_picture_path)) {
                $errors[] = "Failed to save profile picture.";
                $profile_picture_path = null; // Reset path on failure
            }
        }
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt->close();
    }

    // --- If no errors, proceed with registration ---
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role_name = 'User';
        $stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $role_id = $result->fetch_assoc()['role_id'];

            $sql = "INSERT INTO users (role_id, first_name, last_name, email, password_hash, address, phone, sex, profile_picture_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssss", $role_id, $first_name, $last_name, $email, $password_hash, $address, $phone, $sex, $profile_picture_path);

            if ($stmt->execute()) {
                $success_message = "Registration successful! Your account is now pending approval from an administrator. You will be notified via email once it has been reviewed.";
            } else {
                $errors[] = "Registration failed due to a server error.";
                // Clean up uploaded file if db insert fails
                if ($profile_picture_path && file_exists($profile_picture_path)) {
                    unlink($profile_picture_path);
                }
            }
        } else {
            $errors[] = "Critical error: Default user role not found.";
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Create an Account</h3>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo $error; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php // Hide form on success
                    if (empty($success_message)): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sex" class="form-label">Sex</label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="" selected disabled>Select...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
                            <div class="form-text">Please upload a clear photo for verification.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center py-3">
                    <small>Already have an account? <a href="login.php">Log In</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
