<?php
// Initialize the session
session_start();

// Include database connection and helper files
require_once 'includes/db_connect.php';
require_once 'includes/helpers.php';

$token = $_GET['token'] ?? '';
$message = "";
$email = "";
$show_form = false;

if (empty($token)) {
    $message = '<div class="alert alert-danger">Invalid password reset token.</div>';
} else {
    // We need to find the email associated with the token, but we stored a hashed token.
    // This requires iterating through the tokens, which is not ideal.
    // A better approach is to store the selector and validator separately.
    // For now, we will iterate, but this should be refactored for production.

    $sql_find = "SELECT email, token, expires_at FROM password_resets";
    $result = $mysqli->query($sql_find);
    $found_token = false;

    while ($row = $result->fetch_assoc()) {
        if (password_verify($token, $row['token'])) {
            // Token matches. Check expiry.
            if (time() > $row['expires_at']) {
                $message = '<div class="alert alert-danger">Password reset token has expired.</div>';
            } else {
                $email = $row['email'];
                $show_form = true;
            }
            $found_token = true;
            break;
        }
    }

    if (!$found_token) {
        $message = '<div class="alert alert-danger">Invalid password reset token.</div>';
    }
}

// Handle form submission for new password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_email = $_POST['email'];

    if (empty($password) || empty($confirm_password)) {
        $message = '<div class="alert alert-danger">Please enter and confirm your new password.</div>';
        $show_form = true; // Keep the form visible
    } elseif ($password !== $confirm_password) {
        $message = '<div class="alert alert-danger">Passwords do not match.</div>';
        $show_form = true; // Keep the form visible
    } else {
        // Passwords are valid, update the user's password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql_update = "UPDATE users SET password = ? WHERE email = ?";
        if ($stmt_update = $mysqli->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $hashed_password, $user_email);
            if ($stmt_update->execute()) {
                // Password updated, now delete the reset token
                $sql_delete = "DELETE FROM password_resets WHERE email = ?";
                if ($stmt_delete = $mysqli->prepare($sql_delete)) {
                    $stmt_delete->bind_param("s", $user_email);
                    $stmt_delete->execute();
                    $stmt_delete->close();
                }

                $_SESSION['password_reset_success'] = "Your password has been reset successfully. Please login.";
                header("location: login.php");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Failed to update password. Please try again.</div>';
                $show_form = true;
            }
            $stmt_update->close();
        }
    }
}

// Include the header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Reset Password</h2>

        <?php if (!empty($message)) echo $message; ?>

        <?php if ($show_form): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . urlencode($token); ?>" method="post">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="form-group mb-3">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="reset_password" class="btn btn-primary" value="Reset Password">
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>
