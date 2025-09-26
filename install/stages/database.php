<?php
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];

    // Suppress errors for the initial connection attempt
    $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($mysqli->connect_error) {
        $error = "Database connection failed: " . $mysqli->connect_error;
    } else {
        // Connection successful. Store details in the session and move to the next stage.
        $_SESSION['db_host'] = $db_host;
        $_SESSION['db_user'] = $db_user;
        $_SESSION['db_pass'] = $db_pass;
        $_SESSION['db_name'] = $db_name;

        // Redirect to the admin setup stage
        header('Location: index.php?stage=admin');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Database Details</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <p>Please enter your database connection details. The database must be created beforehand.</p>

                <form method="POST">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                    </div>
                    <button type="submit" class="btn btn-primary">Connect & Continue</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>