<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";
$username = "";
$email = "";
$role = "customer";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // --- Validation ---
    if(empty($username) || empty($email) || empty($password)){
        $message = '<div class="alert alert-danger">Username, email, and password are required.</div>';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = '<div class="alert alert-danger">Invalid email format.</div>';
    } else {
        // Check if username or email already exists
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            if($stmt_check->num_rows > 0){
                $message = '<div class="alert alert-danger">Username or email already exists.</div>';
            }
            $stmt_check->close();
        }

        // If no errors, proceed to insert
        if(empty($message)){
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            if($stmt_insert = $mysqli->prepare($sql_insert)){
                $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);
                if($stmt_insert->execute()){
                    $_SESSION['user_updated_message'] = "User created successfully.";
                    header("location: manage_users.php");
                    exit;
                } else {
                    $message = '<div class="alert alert-danger">Error creating user. Please try again.</div>';
                }
                $stmt_insert->close();
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add New User</h1>
    <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus"></i> Enter New User Details
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
                <div class="form-text">Enter a password for the new user.</div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="customer" <?php if($role == 'customer') echo 'selected'; ?>>Customer</option>
                    <option value="admin" <?php if($role == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <hr>
            <div class="d-flex justify-content-end">
                 <a href="manage_users.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
