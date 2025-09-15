<?php
// Stage 2: Database Credentials

$pageTitle = "Stage 2: Database Configuration";

// Check for any error messages from a failed attempt at the next stage
$error_message = $_SESSION['db_error'] ?? null;
unset($_SESSION['db_error']); // Clear the error after displaying it

// Pre-fill form with session data if it exists (e.g., on validation failure)
$db_host = $_SESSION['db_details']['host'] ?? 'localhost';
$db_name = $_SESSION['db_details']['name'] ?? '';
$db_user = $_SESSION['db_details']['user'] ?? '';
$db_pass = $_SESSION['db_details']['pass'] ?? '';

?>

<h1>Stage 2: Database Configuration</h1>
<p>Please provide your database credentials. The installer will use these to connect to your database and set up the necessary tables.</p>
<hr>

<?php if ($error_message): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?> Please check your details and try again.
    </div>
<?php endif; ?>

<form action="index.php?action=savedb" method="POST">
    <div>
        <label for="db_host">Database Host</label>
        <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
    </div>
    <div>
        <label for="db_name">Database Name</label>
        <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
    </div>
    <div>
        <label for="db_user">Database Username</label>
        <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
    </div>
    <div>
        <label for="db_pass">Database Password</label>
        <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
    </div>
    <hr>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <a href="index.php?action=reset" class="btn" style="background-color: #6c757d;">&laquo; Back</a>
        <button type="submit" class="btn">Test Connection & Install &raquo;</button>
    </div>
</form>
