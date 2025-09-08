<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';

$message = "";

// Handle Delete Subscription
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    $sub_id_to_delete = $_GET['id'];
    $sql_delete = "DELETE FROM user_subscriptions WHERE id = ?";
    if($stmt_delete = $mysqli->prepare($sql_delete)){
        $stmt_delete->bind_param("i", $sub_id_to_delete);
        if($stmt_delete->execute()){
            $message = '<div class="alert alert-success">Subscription deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting subscription.</div>';
        }
        $stmt_delete->close();
    }
}

// Search and Fetch
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT us.id, u.username, p.name as class_name, us.status, us.expires_at, us.created_at
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        JOIN products p ON us.product_id = p.id";

$params = [];
$param_types = "";

if(!empty($search_query)){
    $sql .= " WHERE (u.username LIKE ? OR p.name LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = &$search_term;
    $params[] = &$search_term;
    $param_types .= "ss";
}

$sql .= " ORDER BY us.created_at DESC";

if($stmt = $mysqli->prepare($sql)){
    if(!empty($search_query)){
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $subscriptions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $subscriptions = [];
    $message .= '<div class="alert alert-danger">Error fetching subscriptions.</div>';
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Subscriptions</h1>
    <a href="add_subscription.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Subscription</a>
</div>

<?php echo $message; ?>

<!-- Search Form -->
<div class="card mb-3">
    <div class="card-body">
        <form action="manage_subscriptions.php" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by user or class name..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <?php if(!empty($search_query)): ?>
                <a href="manage_subscriptions.php" class="btn btn-secondary ms-2"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-id-card"></i> All User Subscriptions
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Expires On</th>
                        <th>Created On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($subscriptions) > 0): ?>
                        <?php foreach ($subscriptions as $sub): ?>
                        <tr>
                            <td><?php echo $sub['id']; ?></td>
                            <td><?php echo htmlspecialchars($sub['username']); ?></td>
                            <td><?php echo htmlspecialchars($sub['class_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($sub['status'] === 'active') ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($sub['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo $sub['expires_at'] ? date("M j, Y", strtotime($sub['expires_at'])) : 'Never'; ?></td>
                            <td><?php echo date("M j, Y", strtotime($sub['created_at'])); ?></td>
                            <td class="text-end">
                                <!-- Edit functionality can be added here later -->
                                <a href="manage_subscriptions.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subscription?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No subscriptions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include admin footer
include 'includes/footer.php';
?>
