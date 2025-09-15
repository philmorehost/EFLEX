<?php
// Check if the config file exists. If not, redirect to the installer.
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: install/');
    exit;
}

$pageTitle = "Login";
require_once __DIR__ . '/includes/config.php';

$error_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (in_array($_SESSION['role_id'], [1, 2, 3])) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Fetch user from the database
        $sql = "SELECT user_id, first_name, role_id, password_hash FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct, start the session
                session_regenerate_id(true); // Prevent session fixation

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role_id'] = $user['role_id'];

                // Role-based redirection
                if (in_array($user['role_id'], [1, 2, 3])) { // Admin, Super Admin, Staff
                    header("Location: admin/index.php");
                } else { // Regular User
                    header("Location: index.php");
                }
                exit;
            } else {
                // Invalid password
                $error_message = "Invalid email or password.";
            }
        } else {
            // No user found
            $error_message = "Invalid email or password.";
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
                    <h3 class="card-title text-center mb-4">Login to Your Account</h3>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-muted"><small>Forgot Password?</small></a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <small>Don't have an account? <a href="register.php">Sign Up</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
