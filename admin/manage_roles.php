<?php
include 'includes/header.php';

// --- Security Check ---
// Only the main admin should be able to manage roles.
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$message = "";

// --- Define Admin Pages for Permissions ---
$admin_pages = [
    'dashboard.php' => 'Dashboard',
    'manage_products.php' => 'Manage Classes',
    'add_product.php' => 'Add/Edit Class',
    'manage_categories.php' => 'Manage Categories',
    'manage_orders.php' => 'Manage Orders',
    'order_detail.php' => 'Order Details',
    'manage_users.php' => 'Manage Users',
    'add_user.php' => 'Add/Edit User',
    'manage_subscriptions.php' => 'Manage Subscriptions',
    'add_subscription.php' => 'Add Subscription',
    'site_settings.php' => 'Site Settings',
    'manage_drive.php' => 'Manage Google Drive'
    // 'manage_roles.php' is excluded so admins can't lock themselves out.
];


// --- Handle Form Submissions ---

// Add a new role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name']);
    if (!empty($role_name)) {
        $sql_insert = "INSERT INTO roles (role_name) VALUES (?)";
        $stmt_insert = $mysqli->prepare($sql_insert);
        $stmt_insert->bind_param("s", $role_name);
        if ($stmt_insert->execute()) {
            $message = '<div class="alert alert-success">Role added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding role.</div>';
        }
        $stmt_insert->close();
    } else {
        $message = '<div class="alert alert-danger">Role name cannot be empty.</div>';
    }
}

// Update permissions for a role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_permissions'])) {
    $role_id = (int)$_POST['role_id'];
    $permissions = $_POST['permissions'] ?? [];

    // Start transaction
    $mysqli->begin_transaction();
    try {
        // Delete old permissions
        $sql_delete = "DELETE FROM role_permissions WHERE role_id = ?";
        $stmt_delete = $mysqli->prepare($sql_delete);
        $stmt_delete->bind_param("i", $role_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Insert new permissions
        if (!empty($permissions)) {
            $sql_insert = "INSERT INTO role_permissions (role_id, page_name) VALUES (?, ?)";
            $stmt_insert = $mysqli->prepare($sql_insert);
            foreach ($permissions as $page_name) {
                // Sanity check to ensure the page is a valid one
                if (array_key_exists($page_name, $admin_pages)) {
                    $stmt_insert->bind_param("is", $role_id, $page_name);
                    $stmt_insert->execute();
                }
            }
            $stmt_insert->close();
        }
        $mysqli->commit();
        $message = '<div class="alert alert-success">Permissions updated successfully.</div>';
    } catch (Exception $e) {
        $mysqli->rollback();
        $message = '<div class="alert alert-danger">An error occurred while updating permissions.</div>';
    }
}


// Delete a role
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $role_id = $_GET['id'];
    // You might want to add a check here to prevent deletion of roles that are in use.
    $sql_delete = "DELETE FROM roles WHERE id = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("i", $role_id);
    if ($stmt_delete->execute()) {
        $mysqli->query("DELETE FROM role_permissions WHERE role_id = $role_id");
        $message = '<div class="alert alert-success">Role deleted successfully.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting role.</div>';
    }
    $stmt_delete->close();
}


// --- Fetch Data ---
$roles = $mysqli->query("SELECT * FROM roles ORDER BY role_name ASC")->fetch_all(MYSQLI_ASSOC);
// Fetch all permissions and group by role_id for easy lookup
$permissions_result = $mysqli->query("SELECT * FROM role_permissions");
$permissions_by_role = [];
while ($row = $permissions_result->fetch_assoc()) {
    $permissions_by_role[$row['role_id']][] = $row['page_name'];
}

?>

<h1>Manage Staff Roles</h1>
<p class="lead">Create and manage roles for your staff members to control their access to different parts of the admin panel.</p>

<?php echo $message; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus"></i> Add New Role
            </div>
            <div class="card-body">
                <form action="manage_roles.php" method="post">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" id="roleName" name="role_name" class="form-control" placeholder="e.g., Content Manager" required>
                    </div>
                    <button class="btn btn-success w-100" type="submit" name="add_role">Add Role</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-tasks"></i> Existing Roles & Permissions</div>
            <div class="card-body">
                <?php if (count($roles) > 0): ?>
                    <div class="accordion" id="rolesAccordion">
                        <?php foreach ($roles as $role): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?php echo $role['id']; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $role['id']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $role['id']; ?>">
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo $role['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $role['id']; ?>" data-bs-parent="#rolesAccordion">
                                    <div class="accordion-body">
                                        <form action="manage_roles.php" method="post">
                                            <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                            <h5>Permissions for <?php echo htmlspecialchars($role['role_name']); ?></h5>
                                            <div class="row">
                                                <?php
                                                $current_permissions = $permissions_by_role[$role['id']] ?? [];
                                                foreach ($admin_pages as $page_file => $page_label):
                                                    $is_checked = in_array($page_file, $current_permissions);
                                                ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $page_file; ?>" id="perm-<?php echo $role['id']; ?>-<?php echo str_replace('.', '_', $page_file); ?>" <?php if($is_checked) echo 'checked'; ?>>
                                                            <label class="form-check-label" for="perm-<?php echo $role['id']; ?>-<?php echo str_replace('.', '_', $page_file); ?>"><?php echo $page_label; ?></label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                 <a href="manage_roles.php?action=delete&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role? This action cannot be undone.')"><i class="fas fa-trash"></i> Delete Role</a>
                                                <button type="submit" name="update_permissions" class="btn btn-primary">Save Permissions</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No roles created yet. Add one to get started.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
