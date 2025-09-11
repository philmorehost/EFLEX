<?php
$pageTitle = "Edit User";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2]; // Super Admin and Admin
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get User ID and Fetch Data ---
$user_id_to_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id_to_edit) {
    header("Location: users.php?error=invalidid");
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id_to_edit);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: users.php?error=usernotfound");
    exit;
}

// Fetch Roles for Dropdown
$roles = $conn->query("SELECT role_id, role_name FROM roles")->fetch_all(MYSQLI_ASSOC);

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($role_id) || empty($status)) {
        $errors[] = "All fields except password are required.";
    }

    // Check if email (if changed) already exists for another user
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "This email address is already in use by another account.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Prepare the base UPDATE query
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role_id = ?, status = ?";
        $params = [$first_name, $last_name, $email, $role_id, $status];
        $types = "sssis";

        // If password is being updated, add it to the query
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $errors[] = "New password must be at least 8 characters long.";
            } else {
                $sql .= ", password_hash = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
                $types .= "s";
            }
        }

        if (empty($errors)) {
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id_to_edit;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                header("Location: users.php?success=userupdated");
                exit;
            } else {
                $errors[] = "Failed to update user. Please try again.";
            }
            $stmt->close();
        }
    }
    // If there were errors, the form will be re-displayed with the error messages
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Edit User</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Editing User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <p class="mb-0"><?php echo $error; ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="edit-user.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">Leave blank to keep the current password.</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role_id" class="form-label">Role</label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role['role_id']; ?>" <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="suspended" <?php echo ($user['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Update User</button>
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
