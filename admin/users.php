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
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, r.role_name, u.profile_picture_path
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
    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.status, r.role_name, u.profile_picture_path
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
                        <div class="d-flex align-items-center">
                            <h3 class="fs-4 mb-0 me-3">All Users</h3>
                            <div class="btn-group" role="group" aria-label="View Toggle">
                                <button type="button" class="btn btn-outline-secondary active" id="view-list-btn"><i class="bi bi-list-ul"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="view-grid-btn"><i class="bi bi-grid-3x3-gap-fill"></i></button>
                            </div>
                        </div>
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

                    <div class="table-responsive" id="user-list-view">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Photo</th>
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
                                            <td>
                                                <?php
                                                    $pic_path = !empty($user['profile_picture_path']) && file_exists(__DIR__ . '/../' . $user['profile_picture_path'])
                                                        ? '../' . $user['profile_picture_path']
                                                        : '../assets/img/default_avatar.svg';
                                                ?>
                                                <img src="<?php echo htmlspecialchars($pic_path); ?>" alt="User Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            </td>
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

                    <!-- Grid View -->
                    <div id="user-grid-view" style="display: none;">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                            <?php foreach ($users as $user): ?>
                                <div class="col">
                                    <div class="card h-100 text-center shadow-sm">
                                        <?php
                                            $pic_path = !empty($user['profile_picture_path']) && file_exists(__DIR__ . '/../' . $user['profile_picture_path'])
                                                ? '../' . $user['profile_picture_path']
                                                : '../assets/img/default_avatar.svg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($pic_path); ?>" class="card-img-top" alt="User Photo" style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                                            <p class="card-text text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                                            <?php
                                                $status_color = 'secondary'; // Default
                                                if ($user['status'] === 'active') $status_color = 'success';
                                                if ($user['status'] === 'pending') $status_color = 'info text-dark';
                                                if (in_array($user['status'], ['inactive', 'suspended'])) $status_color = 'warning text-dark';
                                            ?>
                                            <span class="badge bg-<?php echo $status_color; ?> mb-3"><?php echo htmlspecialchars(ucfirst($user['status'])); ?></span>
                                        </div>
                                        <div class="card-footer">
                                            <?php if ($user['status'] === 'pending'): ?>
                                                <a href="approve-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                                <a href="deny-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Deny</a>
                                            <?php else: ?>
                                                <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- End Grid View -->

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewListBtn = document.getElementById('view-list-btn');
    const viewGridBtn = document.getElementById('view-grid-btn');
    const listView = document.getElementById('user-list-view');
    const gridView = document.getElementById('user-grid-view');

    viewListBtn.addEventListener('click', function () {
        if (!viewListBtn.classList.contains('active')) {
            listView.style.display = 'block';
            gridView.style.display = 'none';
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
        }
    });

    viewGridBtn.addEventListener('click', function () {
        if (!viewGridBtn.classList.contains('active')) {
            listView.style.display = 'none';
            gridView.style.display = 'block';
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
