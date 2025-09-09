<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

$pdo = require __DIR__ . '/../../config/database.php';

// --- Role-based Access Control ---
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
    echo '<div class="container-fluid"><div class="alert alert-danger"><strong>Access Denied:</strong> You do not have permission to perform this action.</div></div>';
    require_once __DIR__ . '/../../templates/footer.php';
    exit();
}
// --- End Access Control ---

$user_id = $_GET['id'] ?? null;
if (!$user_id || !filter_var($user_id, FILTER_VALIDATE_INT)) {
    $_SESSION['errors'] = ["Invalid user ID."];
    header("Location: users.php");
    exit();
}

// Handle form submission for updating a user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role_id = $_POST['role_id'];
    $password = $_POST['password'];
    $validation_errors = [];

    if (empty($name)) $validation_errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $validation_errors[] = 'A valid email is required.';
    if (empty($role_id)) $validation_errors[] = 'Role is required.';

    if (empty($validation_errors)) {
        try {
            $sql = "UPDATE users SET name = ?, email = ?, role_id = ?";
            $params = [$name, $email, $role_id];

            if (!empty($password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['success'] = "User updated successfully!";
            } else {
                $validation_errors[] = 'Failed to update user.';
            }
        } catch (PDOException $e) {
            // Check for duplicate email error
            if ($e->getCode() == 23000) {
                 $validation_errors[] = 'This email address is already in use by another account.';
            } else {
                $validation_errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }

    if (!empty($validation_errors)) {
        $_SESSION['errors'] = $validation_errors;
    }

    header("Location: users.php");
    exit();
}

// Fetch user data for the form
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $roles = $pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['errors'] = ["User not found."];
        header("Location: users.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit User</h1>
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 fw-bold text-primary">Editing user: <?php echo htmlspecialchars($user['name']); ?></h6>
        </div>
        <div class="card-body">
            <form action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp">
                    <div id="passwordHelp" class="form-text">Leave blank to keep the current password.</div>
                </div>
                <div class="mb-3">
                    <label for="role_id" class="form-label">Role</label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role['id']); ?>" <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/../../templates/footer.php';
?>
