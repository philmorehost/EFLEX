<?php
require_once 'templates/header.php';

// If the user is already logged in, redirect them to the dashboard.
if (is_logged_in()) {
    redirect('dashboard.php');
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Forgot Password</h2>
            </div>
            <div class="card-body">
                <?php
                // Display success or error messages
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['reset_link'])) {
                    echo '<div class="alert alert-info"><strong>Password Reset Link:</strong> <a href="' . htmlspecialchars($_SESSION['reset_link']) . '">' . htmlspecialchars($_SESSION['reset_link']) . '</a></div>';
                    unset($_SESSION['reset_link']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                <p>Enter your email address below, and we'll send you a link to reset your password.</p>

                <form method="POST" action="includes/password-reset-request.php">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>