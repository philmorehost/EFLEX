<?php
if (empty($_SESSION['db_host'])) {
    // If DB details are not in session, redirect to database stage
    header('Location: index.php?stage=database');
    exit;
}

$error = null;
$success = null;

// Function to import SQL schema
function import_schema($mysqli, $file) {
    $sql = file_get_contents($file);
    if ($mysqli->multi_query($sql)) {
        // Clear multi_query results
        while ($mysqli->next_result()) {
            if ($mysqli->more_results()) {
                $mysqli->next_result();
            }
        }
        return true;
    }
    return false;
}

// Connect to the database
$mysqli = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);

// Handle form submission for admin account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_user = $_POST['admin_user'];
    $admin_email = $_POST['admin_email'];
    $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

    // Import the database schema first
    if (!import_schema($mysqli, 'schema.sql')) {
        $error = "Error importing database schema: " . $mysqli->error;
    } else {
        // Insert admin user
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
        if ($stmt) {
            $stmt->bind_param('sss', $admin_user, $admin_email, $admin_pass);
            if ($stmt->execute()) {
                // Create the config file
                $config_content = "<?php\n\n";
                $config_content .= "define('DB_HOST', '" . $_SESSION['db_host'] . "');\n";
                $config_content .= "define('DB_USER', '" . $_SESSION['db_user'] . "');\n";
                $config_content .= "define('DB_PASS', '" . $_SESSION['db_pass'] . "');\n";
                $config_content .= "define('DB_NAME', '" . $_SESSION['db_name'] . "');\n";

                if(file_put_contents('../includes/config.php', $config_content)) {
                    header('Location: index.php?stage=complete');
                    exit;
                } else {
                    $error = "Could not write config file.";
                }

            } else {
                $error = "Failed to create admin account: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Failed to prepare statement: " . $mysqli->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Create Admin Account</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <p>The database schema will be imported, and an admin account will be created.</p>

                <form method="POST">
                    <div class="mb-3">
                        <label for="admin_user" class="form-label">Admin Username</label>
                        <input type="text" class="form-control" id="admin_user" name="admin_user" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_pass" class="form-label">Admin Password</label>
                        <input type="password" class="form-control" id="admin_pass" name="admin_pass" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Account & Finish</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>