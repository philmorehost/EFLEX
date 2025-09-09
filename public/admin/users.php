<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
$pdo = require __DIR__ . '/../../config/database.php';
$is_super_admin = false;
if (isset($_SESSION['user_role_id'])) {
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$_SESSION['user_role_id']]);
    $role = $stmt->fetchColumn();
    if ($role === 'Super Admin') {
        $is_super_admin = true;
    }
}
if (!$is_super_admin) {
    echo '<div class="container-fluid"><div class="alert alert-danger"><strong>Access Denied:</strong> You do not have permission to view this page.</div></div>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit();
}

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Handle Create User ---
    if ($action === 'create_user') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role_id = $_POST['role_id'];
        $validation_errors = [];

        if (empty($name)) $validation_errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $validation_errors[] = 'A valid email is required.';
        if (empty($password)) $validation_errors[] = 'Password is required.';
        if (empty($role_id)) $validation_errors[] = 'Role is required.';

        if (empty($validation_errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'An account with this email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$name, $email, $hashed_password, $role_id])) {
                    $_SESSION['success'] = 'User created successfully!';
                } else {
                    $validation_errors[] = 'Failed to create user.';
                }
            }
        }
        if (!empty($validation_errors)) $_SESSION['errors'] = $validation_errors;
    }

    // --- Handle Delete User ---
    if ($action === 'delete_user') {
        $user_id_to_delete = $_POST['user_id'];
        if ($user_id_to_delete == $_SESSION['user_id']) {
            $_SESSION['errors'] = ["You cannot delete your own account."];
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id_to_delete])) {
                $_SESSION['success'] = 'User deleted successfully!';
            } else {
                $_SESSION['errors'] = ['Failed to delete user.'];
            }
        }
    }

    header("Location: users.php");
    exit();
}

// --- Fetch Page Data ---
try {
    $users = $pdo->query("SELECT u.id, u.name, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $roles = $pdo->query("SELECT id, role_name FROM roles ORDER BY role_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">User Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="fas fa-plus me-2"></i>Create New User</button>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All System Users</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php
                                    $rc = 'bg-secondary';
                                    if ($user['role_name'] === 'Super Admin') $rc = 'bg-danger'; elseif ($user['role_name'] === 'Admin') $rc = 'bg-warning text-dark'; elseif ($user['role_name'] === 'Staff') $rc = 'bg-info text-dark';
                                    ?><span class="badge <?php echo $rc; ?>"><?php echo htmlspecialchars($user['role_name']); ?></span>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger delete-user-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo htmlspecialchars($user['name']); ?>"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form action="users.php" method="POST"><input type="hidden" name="action" value="create_user"><div class="mb-3"><label for="name" class="form-label">Full Name</label><input type="text" class="form-control" name="name" required></div><div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" name="email" required></div><div class="mb-3"><label for="password" class="form-label">Password</label><input type="password" class="form-control" name="password" required></div><div class="mb-3"><label for="role_id" class="form-label">Role</label><select class="form-select" name="role_id" required><option value="">Select role...</option><?php foreach ($roles as $role): ?><option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Create User</button></div></form></div></div></div></div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Are you sure you want to delete the user: <strong id="deleteUserName"></strong>?</p><p>This action cannot be undone.</p></div><div class="modal-footer"><form action="users.php" method="POST"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" id="deleteUserId"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete User</button></form></div></div></div></div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteUserModal = document.getElementById('deleteUserModal');
    deleteUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var userName = button.getAttribute('data-user-name');

        var modalUserName = deleteUserModal.querySelector('#deleteUserName');
        var modalUserIdInput = deleteUserModal.querySelector('#deleteUserId');

        modalUserName.textContent = userName;
        modalUserIdInput.value = userId;
    });
});
</script>
