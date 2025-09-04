<?php
// Include admin header
include 'includes/header.php';
require_once '../includes/db_connect.php';
require_once '../includes/google_drive_api.php'; // For revoking permissions

$message = "";
if(isset($_SESSION['sub_update_message'])){
    $message = '<div class="alert alert-success">'.$_SESSION['sub_update_message'].'</div>';
    unset($_SESSION['sub_update_message']);
}

// Handle Delete Subscription
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])){
    $sub_id_to_delete = $_GET['id'];

    // --- Revoke Google Drive Permissions before deleting ---
    // 1. Get user_id and product_id from the subscription
    $sql_sub_details = "SELECT user_id, product_id FROM user_subscriptions WHERE id = ?";
    if($stmt_sub = $mysqli->prepare($sql_sub_details)){
        $stmt_sub->bind_param("i", $sub_id_to_delete);
        $stmt_sub->execute();
        $result_sub = $stmt_sub->get_result();
        if($sub_details = $result_sub->fetch_assoc()){
            $user_id = $sub_details['user_id'];
            $product_id = $sub_details['product_id'];

            // 2. Get user's email
            $user_email = '';
            $sql_user = "SELECT email FROM users WHERE id = ?";
            if($stmt_user = $mysqli->prepare($sql_user)){
                $stmt_user->bind_param("i", $user_id);
                $stmt_user->execute();
                $stmt_user->bind_result($user_email);
                $stmt_user->fetch();
                $stmt_user->close();
            }

            // 3. Get file IDs for the product
            $file_ids = [];
            $sql_files = "SELECT google_drive_file_id FROM product_google_drive_files WHERE product_id = ?";
            if($stmt_files = $mysqli->prepare($sql_files)){
                $stmt_files->bind_param("i", $product_id);
                $stmt_files->execute();
                $result_files = $stmt_files->get_result();
                while($row = $result_files->fetch_assoc()){
                    $file_ids[] = $row['google_drive_file_id'];
                }
                $stmt_files->close();
            }

            // 4. Revoke permission for each file
            if(!empty($user_email) && !empty($file_ids)){
                foreach($file_ids as $file_id){
                    $permission_id = get_permission_id_for_user($file_id, $user_email);
                    revoke_file_permission($file_id, $permission_id);
                }
            }
        }
        $stmt_sub->close();
    }
    // --- End of Revoke Permissions ---


    // Now, delete the subscription record from the database
    $sql_delete = "DELETE FROM user_subscriptions WHERE id = ?";
    if($stmt_delete = $mysqli->prepare($sql_delete)){
        $stmt_delete->bind_param("i", $sub_id_to_delete);
        if($stmt_delete->execute()){
            $message = '<div class="alert alert-success">Subscription deleted and file access revoked successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting subscription record from the database.</div>';
        }
        $stmt_delete->close();
    }
}

// Fetch all subscriptions with user and product names
$sql = "SELECT us.id, u.username, p.name as product_name, us.status, us.expires_at, us.created_at
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        JOIN products p ON us.product_id = p.id
        ORDER BY us.created_at DESC";
$result = $mysqli->query($sql);
$subscriptions = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Subscriptions</h1>
    <a href="add_subscription.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Subscription</a>
</div>

<?php echo $message; ?>

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
                        <th>Product</th>
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
                            <td><?php echo htmlspecialchars($sub['product_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($sub['status'] === 'active') ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($sub['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo $sub['expires_at'] ? date("M j, Y", strtotime($sub['expires_at'])) : 'Never'; ?></td>
                            <td><?php echo date("M j, Y", strtotime($sub['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="edit_subscription.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="manage_subscriptions.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subscription? This will also revoke the user\'s access to the associated files.')"><i class="fas fa-trash"></i> Delete</a>
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
