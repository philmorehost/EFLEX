<?php
$pageTitle = "Edit Role";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) { // Super Admin only
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get Role ID and Fetch Data ---
$role_id_to_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$role_id_to_edit) {
    header("Location: roles.php?error=invalidid");
    exit;
}

// Fetch role details
$role_stmt = $conn->prepare("SELECT * FROM roles WHERE role_id = ?");
$role_stmt->bind_param("i", $role_id_to_edit);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

if (!$role) {
    header("Location: roles.php?error=rolenotfound");
    exit;
}

// Fetch all permissions, grouped by category
$permissions_sql = "SELECT p.permission_id, p.permission_name, p.description, c.category_name
                    FROM permissions p
                    JOIN permission_categories c ON p.category_id = c.category_id
                    ORDER BY c.category_name, p.permission_id";
$permissions_result = $conn->query($permissions_sql);
$permissions_by_category = [];
while ($row = $permissions_result->fetch_assoc()) {
    $permissions_by_category[$row['category_name']][] = $row;
}

// Fetch current permissions for this role
$current_permissions_stmt = $conn->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$current_permissions_stmt->bind_param("i", $role_id_to_edit);
$current_permissions_stmt->execute();
$current_permissions_result = $current_permissions_stmt->get_result();
$current_permissions = array_column($current_permissions_result->fetch_all(MYSQLI_ASSOC), 'permission_id');
$current_permissions_stmt->close();

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role_name = trim($_POST['role_name'] ?? '');
    $posted_permissions = $_POST['permissions'] ?? [];

    if (empty($role_name)) {
        $errors[] = "Role name cannot be empty.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Update role name if it has changed
            if ($role_name !== $role['role_name']) {
                $update_name_stmt = $conn->prepare("UPDATE roles SET role_name = ? WHERE role_id = ?");
                $update_name_stmt->bind_param("si", $role_name, $role_id_to_edit);
                $update_name_stmt->execute();
                $update_name_stmt->close();
            }

            // Delete old permissions
            $delete_stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $delete_stmt->bind_param("i", $role_id_to_edit);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Insert new permissions
            if (!empty($posted_permissions)) {
                $insert_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                foreach ($posted_permissions as $permission_id) {
                    $insert_stmt->bind_param("ii", $role_id_to_edit, $permission_id);
                    $insert_stmt->execute();
                }
                $insert_stmt->close();
            }

            $conn->commit();
            header("Location: roles.php?success=roleupdated");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred while updating the role. " . $e->getMessage();
        }
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
                <h2 class="fs-2 m-0">Edit Role</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>
        <div class="container-fluid px-4">
            <form action="edit-role.php?id=<?php echo $role_id_to_edit; ?>" method="POST">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Role Name</h5>
                            </div>
                            <div class="card-body">
                                <input type="text" class="form-control" name="role_name" value="<?php echo htmlspecialchars($role['role_name']); ?>" <?php if ($role_id_to_edit <= 4) echo 'readonly'; ?>>
                                <?php if ($role_id_to_edit <= 4): ?>
                                    <small class="form-text text-muted">The name of this core role cannot be changed.</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h4 class="mt-4 mb-3">Assign Permissions</h4>
                        <?php foreach ($permissions_by_category as $category => $permissions): ?>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($category); ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission['permission_id']; ?>" id="perm_<?php echo $permission['permission_id']; ?>" <?php echo in_array($permission['permission_id'], $current_permissions) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="perm_<?php echo $permission['permission_id']; ?>">
                                                    <?php echo htmlspecialchars($permission['permission_name']); ?>
                                                </label>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="roles.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
