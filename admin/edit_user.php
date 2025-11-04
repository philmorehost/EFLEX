<?php
// Core dependencies must be included first.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// The admin header starts the session and must be included before the protection logic.
require_once 'partials/admin_header.php';

// Now that the session is started, we can protect the page.
protect_admin_page();

if (!isset($_GET['id'])) {
    redirect('manage_users.php');
}

$user_id = (int)$_GET['id'];
$current_admin_id = get_current_user()['id'];

// Prevent admin from editing their own sensitive details here to avoid lock-out.
// A separate "Profile" page would be better for the admin's own edits.
if ($user_id === $current_admin_id) {
    // Or handle this more gracefully, e.g., disable certain fields.
    // For now, we just redirect.
    redirect('manage_users.php');
}

// Handle "Login as User" action
if (isset($_GET['action']) && $_GET['action'] === 'login_as') {
    // Store the current admin's ID in the session so we can return.
    $_SESSION['admin_id'] = $current_admin_id;
    // Switch the current session to the target user's ID.
    $_SESSION['user_id'] = $user_id;
    redirect('../dashboard.php');
}

// Fetch the user's data to populate the form.
$stmt = $mysqli->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    redirect('manage_users.php');
}

$errors = [];
$success = null;

// Handle form submission for updating user details.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email)) {
        $errors[] = "Username and email cannot be empty.";
    }

    if (empty($errors)) {
        if (!empty($password)) {
            // If a new password is provided, hash it and update it.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param('sssi', $username, $email, $hashed_password, $user_id);
        } else {
            // If no new password, only update username and email.
            $stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param('ssi', $username, $email, $user_id);
        }

        if ($stmt->execute()) {
            $success = "User details updated successfully.";
            // Refresh user data to show the changes immediately.
            $stmt = $mysqli->prepare("SELECT id, username, email FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errors[] = "Failed to update user details: " . $stmt->error;
        }
    }
}
?>

<h1 class="h3 mb-2 text-gray-800">Edit User: <?php echo htmlspecialchars($user['username']); ?></h1>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="password" class="form-label">Set New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep the user's current password.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User Details</button>
                    <a href="manage_users.php" class="btn btn-secondary">Back to User List</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Admin Actions</h6>
            </div>
            <div class="card-body">
                <p>Log in to this user's account to view their dashboard or troubleshoot issues.</p>
                <a href="edit_user.php?id=<?php echo $user_id; ?>&action=login_as" class="btn btn-warning w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login as <?php echo htmlspecialchars($user['username']); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'partials/admin_footer.php';
?>