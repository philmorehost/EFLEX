<?php
session_start();
require_once __DIR__ . '/../templates/header.php';

// This page is accessible to all logged-in users.
// The header already checks if a user is logged in.

$pdo = require __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

// --- Handle Form Submission ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $validation_errors = [];

    if (empty($name)) $validation_errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $validation_errors[] = 'A valid email is required.';

    // Check if email is being changed and if the new one is already taken
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $validation_errors[] = 'This email address is already in use by another account.';
    }

    // Check password fields only if a new password is being set
    if (!empty($password)) {
        if (strlen($password) < 8) $validation_errors[] = 'Password must be at least 8 characters long.';
        if ($password !== $password_confirm) $validation_errors[] = 'Passwords do not match.';
    }

    if (empty($validation_errors)) {
        try {
            $sql = "UPDATE users SET name = ?, email = ?";
            $params = [$name, $email];
            if (!empty($password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['success'] = "Your profile has been updated successfully!";
                // Update session name in case it changed
                $_SESSION['user_name'] = $name;
            } else {
                $validation_errors[] = 'Failed to update profile.';
            }
        } catch (PDOException $e) {
            $validation_errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    $_SESSION['errors'] = $validation_errors;
    header("Location: profile.php");
    exit();
}


// --- Fetch current user data for the form ---
try {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) die("Could not find user data.");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">My Profile</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Update Your Details</h6></div>
        <div class="card-body">
            <form action="profile.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <hr>
                <h5 class="mb-3">Change Password</h5>
                <p class="text-muted">Leave the password fields blank to keep your current password.</p>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password">
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="password_confirm">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
