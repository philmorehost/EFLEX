<?php
// Core dependencies must be included first.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// The admin header starts the session and must be included before the protection logic.
require_once 'partials/admin_header.php';

// Now that the session is started, we can protect the page.
protect_admin_page();

// Handle actions like suspend/unsuspend from GET requests.
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    $current_user_id = get_current_user()['id'];

    // Prevent admin from suspending their own account.
    if ($user_id !== $current_user_id) {
        if ($action === 'suspend') {
            $stmt = $mysqli->prepare("UPDATE users SET suspended = 1 WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
        } elseif ($action === 'unsuspend') {
            $stmt = $mysqli->prepare("UPDATE users SET suspended = 0 WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
        }
    }
    redirect('manage_users.php');
}

// Fetch all users from the database to display in the table.
$result = $mysqli->query("SELECT id, username, email, created_at, suspended FROM users ORDER BY created_at DESC");
?>

<h1 class="h3 mb-2 text-gray-800">Manage Users</h1>
<p class="mb-4">Here you can view, edit, and suspend user accounts.</p>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Registered Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered On</th>
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
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['suspended']): ?>
                                    <span class="badge bg-danger">Suspended</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($user['id'] !== get_current_user()['id']): // Admin can't suspend self ?>
                                    <?php if ($user['suspended']): ?>
                                        <a href="manage_users.php?action=unsuspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Unsuspend</a>
                                    <?php else: ?>
                                        <a href="manage_users.php?action=suspend&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Suspend</a>
                                    <?php endif; ?>
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