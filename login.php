<?php
require_once 'templates/header.php';

// If the user is already logged in, redirect them to the dashboard.
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $errors[] = 'Both email and password are required.';
    } else {
        // Fetch the user from the database by email.
        $stmt = $mysqli->prepare("SELECT id, password, suspended FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify the user exists and the password is correct.
        if ($user && password_verify($password, $user['password'])) {
            // Check if the account is suspended.
            if ($user['suspended']) {
                $errors[] = 'Your account has been suspended. Please contact support.';
            } else {
                // Password is correct, log the user in.
                $_SESSION['user_id'] = $user['id'];
                redirect('dashboard.php');
            }
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>