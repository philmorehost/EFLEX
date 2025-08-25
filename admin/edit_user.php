<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Include database connection file
require_once "../includes/db_connect.php";

// Check if user ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("location: manage_users.php");
    exit;
}
$user_id_to_edit = $_GET['id'];

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])){
    $new_role = $_POST['role'];
    $user_id = $_POST['user_id'];

    // You can't change your own role for safety
    if($user_id == $_SESSION['id']){
        // Or show an error message
        header("location: manage_users.php");
        exit;
    }

    $sql_update = "UPDATE users SET role = ? WHERE id = ?";
    if($stmt_update = $mysqli->prepare($sql_update)){
        $stmt_update->bind_param("si", $new_role, $user_id);
        if($stmt_update->execute()){
            header("location: manage_users.php");
            exit;
        } else {
            echo "Error updating role.";
        }
        $stmt_update->close();
    }
}

// Fetch user data
$sql_user = "SELECT username, email, role FROM users WHERE id = ?";
$user = null;
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Role</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Navbar -->
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="collapse navbar-collapse">
             <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link active" href="manage_users.php">Users</a></li>
            </ul>
            <ul class="navbar-nav ms-auto"><li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li></ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Edit User Role</h2>
    <div class="card">
        <div class="card-body">
            <form action="edit_user.php?id=<?php echo $user_id_to_edit; ?>" method="post">
                <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="customer" <?php if($user['role'] == 'customer') echo 'selected'; ?>>Customer</option>
                        <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" name="update_role" class="btn btn-primary">Update Role</button>
                <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
