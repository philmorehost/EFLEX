<?php
$pageTitle = "Role Management";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
// This page is for Super Admin only
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
if ($_SESSION['role_id'] != 1) { // 1 = Super Admin
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch all roles with user and permission counts ---
$sql = "SELECT
            r.role_id,
            r.role_name,
            (SELECT COUNT(*) FROM users WHERE role_id = r.role_id) as user_count,
            (SELECT COUNT(*) FROM role_permissions WHERE role_id = r.role_id) as permission_count
        FROM roles r
        ORDER BY r.role_id ASC";
$result = $conn->query($sql);
$roles = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Role Management</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fs-4 mb-0">All Roles</h3>
                        <a href="add-role.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Add New Role
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Role Name</th>
                                    <th scope="col">Users</th>
                                    <th scope="col">Permissions</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <th scope="row"><?php echo $role['role_id']; ?></th>
                                        <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                                        <td><?php echo $role['user_count']; ?></td>
                                        <td><?php echo $role['permission_count']; ?></td>
                                        <td>
                                            <a href="edit-role.php?id=<?php echo $role['role_id']; ?>" class="btn btn-sm btn-outline-primary">Edit Permissions</a>
                                            <?php if ($role['role_id'] > 4): // Don't allow deleting the 4 core roles ?>
                                                <a href="delete-role.php?id=<?php echo $role['role_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? Deleting a role cannot be undone.');">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
