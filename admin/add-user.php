<?php
$pageTitle = "Add New User";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2]; // Only Super Admin and Admin can add users
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    // Redirect staff or other roles to a 'permission denied' page or the dashboard
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch Roles for Dropdown ---
$roles_sql = "SELECT role_id, role_name FROM roles";
$roles_result = $conn->query($roles_sql);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);

$errors = [];
$success_message = '';

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $status = $_POST['status'] ?? 'active';

    // Basic Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role_id)) {
        $errors[] = "All fields except status are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "An account with this email already exists.";
    }
    $stmt->close();

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssis", $first_name, $last_name, $email, $password_hash, $role_id, $status);

        if ($stmt->execute()) {
            // Redirect to user list on success
            header("Location: users.php?success=useradded");
            exit;
        } else {
            $errors[] = "Failed to add user. Please try again.";
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Add New User</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">User Details</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <p class="mb-0"><?php echo $error; ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="add-user.php" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">A temporary password. The user should change it upon first login.</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role_id" class="form-label">Role</label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <option value="">Select a role...</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Add User</button>
                                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
