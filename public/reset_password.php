<?php
session_start();
require_once __DIR__ . '/../config/config.php';
$pdo = require __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? null;
$token_hash = hash('sha256', $token);
$is_valid_token = false;
$user_email = null;

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token_hash]);
    $reset_request = $stmt->fetch();

    if ($reset_request && time() < $reset_request['expires_at']) {
        $is_valid_token = true;
        $user_email = $reset_request['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['token'] ?? '';
    if (!$is_valid_token || $posted_token !== $token) {
        die("Invalid or expired token.");
    }

    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (strlen($password) < 8) {
        $_SESSION['errors'] = ['Password must be at least 8 characters long.'];
    } elseif ($password !== $password_confirm) {
        $_SESSION['errors'] = ['Passwords do not match.'];
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();
            // Update user's password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $user_email]);

            // Delete the reset token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$user_email]);

            $pdo->commit();
            $_SESSION['success_message'] = "Your password has been reset successfully. Please log in.";
            header("Location: login.php");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['errors'] = ["An error occurred. Please try again."];
        }
    }
    // Redirect back to the same page to show errors
    header("Location: reset_password.php?token=" . $token);
    exit();
}

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CBT Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f0f2f5; } .container { max-width: 450px; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); } </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Reset Your Password</h1>

        <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <?php if ($is_valid_token): ?>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">This password reset link is invalid or has expired. Please request a new one.</div>
            <div class="text-center mt-3"><a href="forgot_password.php">Request New Link</a></div>
        <?php endif; ?>
    </div>
</body>
</html>
