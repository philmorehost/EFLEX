<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
protect_admin_page();

// Handle suspend/unsuspend actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];

    if ($action === 'suspend') {
        $stmt = $mysqli->prepare("UPDATE users SET suspended = 1 WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
    } elseif ($action === 'unsuspend') {
        $stmt = $mysqli->prepare("UPDATE users SET suspended = 0 WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
    }
    redirect('manage_users.php');
}

// Fetch all users
$result = $mysqli->query("SELECT id, username, email, created_at, suspended FROM users ORDER BY created_at DESC");

require_once 'partials/admin_header.php';
?>

<h1 class="h3 mb-2 text-gray-800">Manage Users</h1>
<p class="mb-4">Here you can view, edit, and suspend user accounts.</p>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['suspended']): ?>
                                    <span class="badge bg-danger">Suspended</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                <?php if ($user['suspended']): ?>
                                    <a href="manage_users.php?action=unsuspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Unsuspend</a>
                                <?php else: ?>
                                    <a href="manage_users.php?action=suspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Suspend</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'partials/admin_footer.php';
?>