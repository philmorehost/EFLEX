<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";
$user_id_to_edit = 0;
$user = null;

// Check if user ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: manage_users.php");
    exit;
}
$user_id_to_edit = $_GET['id'];

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])){
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $user_type = $_POST['user_type'];
    $password = $_POST['password'];
    $role_id = ($user_type === 'staff' && isset($_POST['role_id'])) ? (int)$_POST['role_id'] : null;

    // --- Validation ---
    if(empty($username) || empty($email)){
        $message = '<div class="alert alert-danger">Username and email are required.</div>';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = '<div class="alert alert-danger">Invalid email format.</div>';
    } elseif($user_type === 'staff' && empty($role_id)) {
        $message = '<div class="alert alert-danger">A role must be assigned for staff members.</div>';
    } else {
        // Check if username or email already exists for another user
        $sql_check = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("ssi", $username, $email, $user_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if($stmt_check->num_rows > 0){
                $message = '<div class="alert alert-danger">Username or email already taken by another user.</div>';
            }
            $stmt_check->close();
        }
    }

    // Prevent an admin from changing their own role to non-admin
    if($user_id == $_SESSION['id'] && $user_type !== 'admin'){
        $message = '<div class="alert alert-warning">You cannot remove your own admin privileges.</div>';
    }

    // If no errors, proceed to update
    if(empty($message)){
        if(!empty($password)){
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE users SET username = ?, email = ?, role = ?, role_id = ?, password = ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("sssisi", $username, $email, $user_type, $role_id, $hashed_password, $user_id);
        } else {
            // Update without changing password
            $sql_update = "UPDATE users SET username = ?, email = ?, role = ?, role_id = ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("sssii", $username, $email, $user_type, $role_id, $user_id);
        }

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

// Fetch user data for the form
$sql_user = "SELECT username, email, role, role_id FROM users WHERE id = ?";
if($stmt_user = $mysqli->prepare($sql_user)){
    $stmt_user->bind_param("i", $user_id_to_edit);
    $stmt_user->execute();
    $result = $stmt_user->get_result();
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
    } else {
        $_SESSION['user_updated_message'] = '<div class="alert alert-danger">User not found.</div>';
        header("location: manage_users.php");
        exit;
    }
    $stmt_user->close();
}

// Fetch roles for the dropdown
$roles_result = $mysqli->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);
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
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" id="password">
                <div class="form-text">Leave blank to keep the current password.</div>
            </div>
            <div class="mb-3">
                <label for="user_type" class="form-label">User Type</label>
                <select name="user_type" id="user_type" class="form-select">
                    <option value="customer" <?php if($user['role'] == 'customer') echo 'selected'; ?>>Customer</option>
                    <option value="staff" <?php if($user['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                    <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3 <?php echo ($user['role'] !== 'staff') ? 'd-none' : ''; ?>" id="role-assignment-container">
                <label for="role_id" class="form-label">Assign Role</label>
                <select name="role_id" id="role_id" class="form-select">
                    <option value="">Select a role...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php if($user['role_id'] == $role['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr>
            <div class="d-flex justify-content-end">
                 <a href="manage_users.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeSelect = document.getElementById('user_type');
    const roleContainer = document.getElementById('role-assignment-container');

    function toggleRoleContainer() {
        if (userTypeSelect.value === 'staff') {
            roleContainer.classList.remove('d-none');
        } else {
            roleContainer.classList.add('d-none');
        }
    }

    // Initial check on page load is handled by server-side class rendering.
    // Listen for changes
    userTypeSelect.addEventListener('change', toggleRoleContainer);
});
</script>

<?php
// Include admin footer
include 'includes/footer.php';
?>
