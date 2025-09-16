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

// --- Fetch users from the database ---
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, r.role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?
            ORDER BY u.user_id ASC";
    $stmt = $conn->prepare($sql);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("ss", $like_term, $like_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, r.role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.user_id ASC";
    $result = $conn->query($sql);
}
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
                        <div>
                             <a href="import-students.php" class="btn btn-outline-success">
                                <i class="bi bi-upload me-2"></i>Import Students
                            </a>
                            <a href="export-students.php" class="btn btn-outline-info">
                                <i class="bi bi-download me-2"></i>Export Students
                            </a>
                            <a href="add-user.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Add New User
                            </a>
                        </div>
                    </div>

                    <!-- Search and Filter Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form action="users.php" method="get" class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <label for="search" class="visually-hidden">Search</label>
                                    <input type="search" class="form-control" id="search" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                                <div class="col-md-2">
                                     <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
                                </div>
                            </form>
                        </div>
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
                                                <?php
                                                    $status_color = 'secondary'; // Default
                                                    if ($user['status'] === 'active') $status_color = 'success';
                                                    if ($user['status'] === 'pending') $status_color = 'info text-dark';
                                                    if (in_array($user['status'], ['inactive', 'suspended'])) $status_color = 'warning text-dark';
                                                ?>
                                                <span class="badge bg-<?php echo $status_color; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['status'] === 'pending'): ?>
                                                    <a href="approve-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                                    <a href="deny-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to deny and delete this user?');">Deny</a>
                                                <?php else: ?>
                                                    <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                                                <?php endif; ?>
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
