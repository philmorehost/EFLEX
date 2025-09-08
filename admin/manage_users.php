<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Handle Delete User
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    $user_id_to_delete = $_GET['id'];

    if($user_id_to_delete == $_SESSION['id']){
        $message = '<div class="alert alert-danger">Error: You cannot delete your own account.</div>';
    } else {
        // Check if the user has any orders
        $sql_check = "SELECT COUNT(*) FROM orders WHERE user_id = ?";
        if($stmt_check = $mysqli->prepare($sql_check)){
            $stmt_check->bind_param("i", $user_id_to_delete);
            $stmt_check->execute();
            $stmt_check->bind_result($order_count);
            $stmt_check->fetch();
            $stmt_check->close();

            if($order_count > 0){
                $message = '<div class="alert alert-warning">Cannot delete user. This user has existing orders. Please reassign or delete their orders first.</div>';
            } else {
                // No orders, proceed with deletion
                $sql_delete = "DELETE FROM users WHERE id = ?";
                if($stmt_delete = $mysqli->prepare($sql_delete)){
                    $stmt_delete->bind_param("i", $user_id_to_delete);
                    if($stmt_delete->execute()){
                        $message = '<div class="alert alert-success">User deleted successfully.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error deleting user.</div>';
                    }
                    $stmt_delete->close();
                }
            }
        }
    }
}

if(isset($_SESSION['user_updated_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['user_updated_message'].'</div>';
    unset($_SESSION['user_updated_message']);
}


// Search and Pagination variables
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Base SQL and parameters
$sql_base = "
    FROM
        users u
    LEFT JOIN (
        SELECT
            user_id,
            status,
            expires_at,
            ROW_NUMBER() OVER(PARTITION BY user_id ORDER BY created_at DESC) as rn
        FROM
            user_subscriptions
    ) sub ON u.id = sub.user_id AND sub.rn = 1
";
$sql_where = "";
$params = [];
$param_types = "";

if(!empty($search_query)){
    $sql_where = " WHERE (u.username LIKE ? OR u.email LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = &$search_term;
    $params[] = &$search_term;
    $param_types .= "ss";
}


// Get total number of users
$total_records_sql = "SELECT COUNT(DISTINCT u.id) " . $sql_base . $sql_where;
if($stmt_total = $mysqli->prepare($total_records_sql)){
    if(!empty($search_query)){
        $stmt_total->bind_param($param_types, ...$params);
    }
    $stmt_total->execute();
    $total_records_result = $stmt_total->get_result();
    $total_records = $total_records_result->fetch_row()[0];
    $stmt_total->close();
} else {
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);


// Fetch users for the current page
$sql = "
    SELECT
        u.id,
        u.username,
        u.email,
        u.role,
        u.created_at,
        sub.status as subscription_status,
        sub.expires_at as subscription_expiry
    " . $sql_base . $sql_where . "
    ORDER BY
        u.created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = &$records_per_page;
$params[] = &$offset;
$param_types .= "ii";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param($param_types, ...$params);
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
    <a href="add_user.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New User</a>
</div>

<?php echo $message; ?>

<!-- Search Form -->
<div class="card mb-3">
    <div class="card-body">
        <form action="manage_users.php" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <?php if(!empty($search_query)): ?>
                <a href="manage_users.php" class="btn btn-secondary ms-2"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

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
                        <th>Subscription</th>
                        <th>Expires On</th>
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
                                <?php if ($user['subscription_status']): ?>
                                    <span class="badge bg-<?php echo ($user['subscription_status'] === 'active') ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($user['subscription_status'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Subscription</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['subscription_expiry'] ? date("M j, Y", strtotime($user['subscription_expiry'])) : 'N/A'; ?></td>
                            <td class="text-end">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <?php if($user['id'] != $_SESSION['id']): ?>
                                <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action is permanent.')"><i class="fas fa-trash"></i> Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No users found.</td></tr>
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
                    <li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a></li>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>"><a class="page-link" href="manage_users.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="manage_users.php?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
