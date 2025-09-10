<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/helpers.php';
    $pdo = require __DIR__ . '/../config/database.php';

    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors'] = ['Please enter a valid email address.'];
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // User exists, generate token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = time() + 3600; // Token expires in 1 hour

            $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $token_hash, $expires_at]);

            // Send the email (simulated)
            $reset_link = BASE_URL . "/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a>. This link will expire in 1 hour.";

            send_email($pdo, $email, $subject, $message);

            $_SESSION['success'] = 'If an account with that email exists, a password reset link has been sent.';
        } else {
            // To prevent user enumeration, show the same message whether the user exists or not.
            $_SESSION['success'] = 'If an account with that email exists, a password reset link has been sent.';
        }
    }
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CBT Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f0f2f5; }
        .container { max-width: 450px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Forgot Password</h1>
        <p class="text-center text-muted mb-4">Enter your email address and we will send you a link to reset your password.</p>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Password Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
