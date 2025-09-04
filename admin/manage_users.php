<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Handle Suspend/Unsuspend User
if(isset($_GET['action']) && ($_GET['action'] == 'suspend' || $_GET['action'] == 'unsuspend') && isset($_GET['id'])){
    $user_id_to_modify = $_GET['id'];
    $new_status = ($_GET['action'] == 'suspend') ? 'suspended' : 'active';

    if($user_id_to_modify == $_SESSION['id']){
        $message = '<div class="alert alert-danger">Error: You cannot change your own status.</div>';
    } else {
        $sql_update = "UPDATE users SET status = ? WHERE id = ?";
        if($stmt_update = $mysqli->prepare($sql_update)){
            $stmt_update->bind_param("si", $new_status, $user_id_to_modify);
            if($stmt_update->execute()){
                $message = '<div class="alert alert-success">User status updated successfully.</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating user status.</div>';
            }
            $stmt_update->close();
        }
    }
}


// Handle Delete User
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    // (Existing delete logic remains here, but might need review later)
}

if(isset($_SESSION['user_updated_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['user_updated_message'].'</div>';
    unset($_SESSION['user_updated_message']);
}
if(isset($_SESSION['user_added_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['user_added_message'].'</div>';
    unset($_SESSION['user_added_message']);
}


// Pagination variables
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Get total number of users
$total_records_result = $mysqli->query("SELECT COUNT(*) FROM users");
$total_records = $total_records_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Fetch users for the current page
$sql = "SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("ii", $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = [];
    $message .= '<div class="alert alert-danger">Error fetching users.</div>';
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Users</h1>
    <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Add New User</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-users"></i> Registered Users
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($user['role'] === 'admin') ? 'danger' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($user['status'] === 'active') ? 'success' : 'warning text-dark'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <?php if($user['id'] != $_SESSION['id']): ?>
                                    <?php if($user['status'] === 'active'): ?>
                                        <a href="manage_users.php?action=suspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to suspend this user?');"><i class="fas fa-ban"></i> Suspend</a>
                                    <?php else: ?>
                                        <a href="manage_users.php?action=unsuspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to unsuspend this user?');"><i class="fas fa-check-circle"></i> Unsuspend</a>
                                    <?php endif; ?>
                                    <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action is permanent.')"><i class="fas fa-trash"></i> Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <?php if($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page-1; ?>">Previous</a></li>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page+1; ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
