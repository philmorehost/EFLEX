<?php
$pageTitle = "Welcome";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Welcome to the <?php echo SITE_NAME; ?></h1>
                    <p class="card-text">Your reliable and user-friendly platform for computer-based testing.</p>
                    <hr>
                    <p class="card-text">Please log in to continue.</p>
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                </div>
            </div>

            <div class="mt-4">
                <p>
                    <small>
                        <a href="<?php echo BASE_URL; ?>admin/" class="text-muted">Admin Login</a>
                    </small>
                </p>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
