<?php
$pageTitle = "User Management";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
// Redirect non-admins to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    session_destroy();
    header("Location: ../login.php?error=accessdenied");
    exit;
}

// --- Fetch all users from the database ---
$sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, r.role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.role_id
        ORDER BY u.user_id ASC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; // Using a separate sidebar include for maintainability ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">User Management</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fs-4 mb-0">All Users</h3>
                        <a href="add-user.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Add New User
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <th scope="row"><?php echo htmlspecialchars($user['user_id']); ?></th>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
