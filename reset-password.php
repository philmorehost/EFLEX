<?php
$pageTitle = "Reset Password";
require_once __DIR__ . '/includes/config.php';

$token = $_REQUEST['token'] ?? ''; // Can be from GET or POST
$message = '';
$message_type = ''; // 'success' or 'danger'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    // --- Validation ---
    if (empty($password) || empty($confirm_password) || empty($token)) {
        $message = "All fields are required.";
        $message_type = 'danger';
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'danger';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'danger';
    } else {
        // --- Token Verification ---
        $token_hash = hash('sha256', $token);
        $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (time() > $row['expires_at']) {
                $message = "This password reset link has expired. Please request a new one.";
                $message_type = 'danger';
            } else {
                // --- Token is valid, update password ---
                $email = $row['email'];
                $new_password_hash = password_hash($password, PASSWORD_DEFAULT);

                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_password_hash, $email);

                if ($update_stmt->execute()) {
                    // --- Invalidate the token ---
                    $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    $delete_stmt->bind_param("s", $email);
                    $delete_stmt->execute();
                    $delete_stmt->close();

                    $message = "Your password has been updated successfully! You can now <a href='login.php' class='alert-link'>log in</a>.";
                    $message_type = 'success';
                } else {
                    $message = "Failed to update password. Please try again.";
                    $message_type = 'danger';
                }
                $update_stmt->close();
            }
        } else {
            $message = "Invalid or expired password reset link.";
            $message_type = 'danger';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Choose a New Password</h3>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                            <?php echo $message; // The message can contain HTML, so not escaping here. ?>
                        </div>
                    <?php endif; ?>

                    <?php // Show form only if the process is not yet successful and a token exists.
                    if ($message_type !== 'success' && !empty($token)): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    <?php // If there's no token and no success message, show an error.
                    elseif (empty($token) && $message_type !== 'success'): ?>
                         <div class="alert alert-danger">
                            Invalid or missing password reset token.
                        </div>
                        <div class="text-center">
                            <a href="forgot-password.php" class="btn btn-secondary">Request a New Reset Link</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
