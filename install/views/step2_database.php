<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketplace Installer - Step 2</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Step 2: Database Configuration</h1>
        <p>Please provide your database connection details below. The installer will attempt to connect and set up the necessary tables.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="index.php?step=3" method="POST">
            <div class="form-group">
                <label for="db_host">Database Host</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name" required>
            </div>
            <div class="form-group">
                <label for="db_user">Database Username</label>
                <input type="text" id="db_user" name="db_user" required>
            </div>
            <div class="form-group">
                <label for="db_pass">Database Password</label>
                <input type="password" id="db_pass" name="db_pass">
            </div>
            <hr>
            <h2>Admin Account</h2>
            <p>Create the first administrator account.</p>
            <div class="form-group">
                <label for="admin_email">Admin Email</label>
                <input type="email" id="admin_email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label for="admin_password">Admin Password</label>
                <input type="password" id="admin_password" name="admin_password" required>
            </div>
            <button type="submit" class="btn">Install Now</button>
        </form>
    </div>
</body>
</html>
