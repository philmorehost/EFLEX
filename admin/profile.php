<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/../includes/config.php';

// --- Auth Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($first_name) || empty($last_name)) {
        $errors[] = "First and last name are required.";
    }

    $sql = "UPDATE users SET first_name = ?, last_name = ?";
    $params = [$first_name, $last_name];
    $types = "ss";

    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        } else {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
    }

    if (empty($errors)) {
        $sql .= " WHERE user_id = ?";
        $params[] = $user_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $success_message = "Your profile has been updated successfully!";
            // Update session variable to reflect name change immediately
            $_SESSION['first_name'] = $first_name;
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
}

// --- Fetch current user data for the form ---
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();


require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">My Profile</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-8">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Update Your Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    <small class="form-text text-muted">Your email address cannot be changed.</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <hr>
                                <h6 class="mt-4">Change Password</h6>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">Leave blank if you don't want to change your password.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
