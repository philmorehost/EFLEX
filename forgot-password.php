<?php
$pageTitle = "Forgot Password";
require_once __DIR__ . '/includes/config.php';

$message = '';
$message_type = ''; // 'success' or 'danger'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = 'danger';
    } else {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, generate a token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = time() + 3600; // Token expires in 1 hour

            // Delete any old tokens for this email
            $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt_delete->bind_param("s", $email);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Store the new token
            $sql_insert = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssi", $email, $token_hash, $expires_at);

            if ($stmt_insert->execute()) {
                // SIMULATE SENDING EMAIL
                // In a real application, you would use a mail library to send this link.
                // For this project, we will display the link directly as a success message.
                $reset_link = BASE_URL . "reset-password.php?token=" . $token;

                $message = "If an account with that email exists, a password reset link has been sent. <br><strong>For demonstration purposes, here is the link:</strong> <a href='{$reset_link}'>Reset Password</a>";
                $message_type = 'success';

            } else {
                $message = "Could not process your request. Please try again later.";
                $message_type = 'danger';
            }
            $stmt_insert->close();
        } else {
            // To prevent user enumeration, show the same message whether the user exists or not.
            $message = "If an account with that email exists, a password reset link has been sent.";
            $message_type = 'info';
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
                    <h3 class="card-title text-center mb-3">Forgot Your Password?</h3>
                    <p class="text-center text-muted mb-4">No problem. Enter your email address and we will send you a link to reset your password.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                            <?php echo $message; // The message can contain HTML, so not escaping here. Be careful with what's in the message. ?>
                        </div>
                    <?php endif; ?>

                    <?php // Hide form on success
                    if ($message_type !== 'success'): ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center py-3">
                    <small><a href="login.php">Back to Login</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
