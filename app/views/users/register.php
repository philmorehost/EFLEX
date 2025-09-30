<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create An Account</h2>
        <p>Please fill out this form to register with us.</p>
        <form action="<?php echo BASE_URL; ?>/users/register" method="post">
            <div class="form-row">
                <div class="col">
                    <div class="form-group">
                        <label for="first_name">First Name: <sup>*</sup></label>
                        <input type="text" name="first_name" class="form-control <?php echo (!empty($data['first_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['first_name']; ?>">
                        <span class="invalid-feedback"><?php echo $data['first_name_err']; ?></span>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="last_name">Last Name: <sup>*</sup></label>
                        <input type="text" name="last_name" class="form-control <?php echo (!empty($data['last_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['last_name']; ?>">
                        <span class="invalid-feedback"><?php echo $data['last_name_err']; ?></span>
                    </div>
                </div>
            </div>
             <div class="form-group">
                <label for="phone">Phone Number: <sup>*</sup></label>
                <input type="tel" name="phone" class="form-control <?php echo (!empty($data['phone_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['phone']; ?>">
                <span class="invalid-feedback"><?php echo $data['phone_err']; ?></span>
            </div>
            <div class="form-group">
                <label for="username">Username: <sup>*</sup></label>
                <input type="text" name="username" class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['username']; ?>">
                <span class="invalid-feedback"><?php echo $data['username_err']; ?></span>
            </div>
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
            <div class="form-group">
                <label for="confirm_password">Confirm Password: <sup>*</sup></label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['confirm_password']; ?>">
                <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
            </div>

            <div class="form-row">
                <div class="col">
                    <input type="submit" value="Register" class="btn btn-success btn-block">
                </div>
                <div class="col">
                    <a href="<?php echo BASE_URL; ?>/users/login" class="btn btn-light btn-block">Have an account? Login</a>
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
    .btn { display: inline-block; font-weight: 400; color: #212529; text-align: center; vertical-align: middle; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-color: transparent; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; text-decoration: none; }
    .btn-block { display: block; width: 100%; }
    .btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
    .btn-light { color: #212529; background-color: #f8f9fa; border-color: #f8f9fa; }
    .form-row { display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px; }
    .col { flex-basis: 0; flex-grow: 1; max-width: 100%; padding: 0 5px; }
</style>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>