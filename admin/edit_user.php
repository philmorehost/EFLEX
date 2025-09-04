<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";
$user_id_to_edit = 0;
$user = null;
$password_err = "";

// Check if user ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: manage_users.php");
    exit;
}
$user_id_to_edit = $_GET['id'];

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])){
    $new_role = $_POST['role'];
    $new_password = $_POST['password'];
    $user_id = $_POST['user_id'];

    // Prevent an admin from changing their own role to non-admin
    if($user_id == $_SESSION['id'] && $new_role !== 'admin'){
        $message = '<div class="alert alert-warning">You cannot remove your own admin privileges.</div>';
    } else {
        // Build the update query
        $sql_parts = [];
        $params = [];
        $types = "";

        // Always update role
        $sql_parts[] = "role = ?";
        $params[] = $new_role;
        $types .= "s";

        // Conditionally update password
        if(!empty($new_password)){
            if(strlen($new_password) < 6){
                $password_err = "Password must have at least 6 characters.";
            } else {
                $sql_parts[] = "password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                $types .= "s";
            }
        }

        if(empty($password_err)) {
            $sql_update = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            if($stmt_update = $mysqli->prepare($sql_update)){
                $stmt_update->bind_param($types, ...$params);
                if($stmt_update->execute()){
                    $_SESSION['user_updated_message'] = "User details updated successfully.";
                    header("location: manage_users.php");
                    exit;
                } else {
                    $message = '<div class="alert alert-danger">Error updating user. Please try again.</div>';
                }
                $stmt_update->close();
            }
        }
    }
}

// Fetch user data for the form
$sql_user = "SELECT username, email, role FROM users WHERE id = ?";
if($stmt_user = $mysqli->prepare($sql_user)){
    $stmt_user->bind_param("i", $user_id_to_edit);
    $stmt_user->execute();
    $result = $stmt_user->get_result();
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
    } else {
        header("location: manage_users.php");
        exit;
    }
    $stmt_user->close();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit User</h1>
    <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> Edit details for <?php echo htmlspecialchars($user['username']); ?>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $user_id_to_edit); ?>" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">

            <h5>Account Details</h5>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="customer" <?php if($user['role'] == 'customer') echo 'selected'; ?>>Customer</option>
                    <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <hr>

            <h5>Update Password</h5>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <div class="form-text">Leave blank to keep the current password.</div>
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <hr>
            <div class="d-flex justify-content-end">
                 <a href="manage_users.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
