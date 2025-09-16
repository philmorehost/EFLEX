<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/../lib/Session.php'; ?>

<div class="auth-container">
    <div class="auth-form-wrapper">
        <h2>Login to Your Account</h2>

        <?php if ($success_message = Session::flash('success_message')): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message = Session::flash('error_message')): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="/login" method="POST" class="auth-form">
            <div class="form-group">
                <label for="login-email">Email Address</label>
                <input type="email" id="login-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>

    <div class="auth-form-wrapper">
        <h2>Create a New Account</h2>
        <form action="/register" method="POST" class="auth-form">
            <div class="form-group">
                <label for="register-username">Username</label>
                <input type="text" id="register-username" name="username" required>
            </div>
            <div class="form-group">
                <label for="register-email">Email Address</label>
                <input type="email" id="register-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="register-password">Password</label>
                <input type="password" id="register-password" name="password" required>
            </div>
            <div class="form-group">
                <label>Account Type</label>
                <select name="user_type">
                    <option value="buyer">I want to buy items</option>
                    <option value="seller">I want to sell items</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-block">Register</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
