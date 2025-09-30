<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <?php flash('register_success'); ?>
        <h2>Login</h2>
        <p>Please fill in your credentials to log in.</p>
        <form action="<?php echo BASE_URL; ?>/users/login" method="post">
            <div class="form-group">
                <label for="email">Email: <sup>*</sup></label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password: <sup>*</sup></label>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>

            <div class="form-row">
                <div class="col">
                    <input type="submit" value="Login" class="btn btn-success btn-block">
                </div>
                <div class="col">
                    <a href="<?php echo BASE_URL; ?>/users/register" class="btn btn-light btn-block">No account? Register</a>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .auth-container { display: flex; justify-content: center; align-items: center; padding: 2rem 0; }
    .auth-card { background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; max-width: 100%; }
    .form-group { margin-bottom: 1rem; }
    .form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; box-sizing: border-box; }
    .form-control.is-invalid { border-color: #dc3545; }
    .invalid-feedback { color: #dc3545; font-size: 80%; }
    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .btn { display: inline-block; font-weight: 400; color: #212529; text-align: center; vertical-align: middle; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-color: transparent; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; text-decoration: none; }
    .btn-block { display: block; width: 100%; }
    .btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
    .btn-light { color: #212529; background-color: #f8f9fa; border-color: #f8f9fa; }
    .form-row { display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px; }
    .col { flex-basis: 0; flex-grow: 1; max-width: 100%; padding: 0 5px; }
</style>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>