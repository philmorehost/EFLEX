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

$message = "";

// Handle Delete User
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    $user_id_to_delete = $_GET['id'];

    // Safety check: do not allow an admin to delete their own account
    if($user_id_to_delete == $_SESSION['id']){
        $message = '<div class="alert alert-danger">You cannot delete your own account.</div>';
    } else {
        // Check if the user has any orders
        $sql_check = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("i", $user_id_to_delete);
            $stmt_check->execute();
            $order_count = $stmt_check->get_result()->fetch_row()[0];
            $stmt_check->close();

            if($order_count > 0){
                $message = '<div class="alert alert-warning">Cannot delete user. This user has existing orders.</div>';
            } else {
                // No orders, proceed with deletion
                $sql_delete = "DELETE FROM users WHERE id = ?";
                if($stmt_delete = $mysqli->prepare($sql_delete)){
                    $stmt_delete->bind_param("i", $user_id_to_delete);
                    if($stmt_delete->execute()){
                        header("location: manage_users.php");
                        exit;
                    } else {
                        $message = '<div class="alert alert-danger">Error deleting user.</div>';
                    }
                    $stmt_delete->close();
                }
            }
        }
    }
}


// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total number of users
$total_records_result = $mysqli->query("SELECT COUNT(*) FROM users");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch users for the current page
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Users</h2>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <div class="card-header">All Users</div>
        <div class="card-body">
            <table class="table table-striped">
                <!-- Table Header -->
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Registered On</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if(count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td class="text-end">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Edit Role</a>
                                <?php if($user['id'] != $_SESSION['id']): // Prevent self-delete button from even showing ?>
                                <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4">
        <?php if($page > 1): ?><li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page-1; ?>">Previous</a></li><?php endif; ?>
        <?php for($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
        <?php if($page < $total_pages): ?><li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page+1; ?>">Next</a></li><?php endif; ?>
      </ul>
    </nav>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
