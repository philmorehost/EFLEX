<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Create Category ---
    if ($action === 'create_category') {
        $category_name = trim($_POST['category_name']);
        if (empty($category_name)) {
            $_SESSION['errors'] = ['Category name cannot be empty.'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
            if ($stmt->execute([$category_name])) {
                $_SESSION['success'] = 'Category created successfully!';
            } else {
                $_SESSION['errors'] = ['Failed to create category. It may already exist.'];
            }
        }
    }

    // --- Update Category ---
    if ($action === 'update_category') {
        $category_id = $_POST['category_id'];
        $category_name = trim($_POST['category_name']);
        if (empty($category_name) || empty($category_id)) {
            $_SESSION['errors'] = ['Category name and ID are required.'];
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
            if ($stmt->execute([$category_name, $category_id])) {
                $_SESSION['success'] = 'Category updated successfully!';
            } else {
                $_SESSION['errors'] = ['Failed to update category.'];
            }
        }
    }

    // --- Delete Category ---
    if ($action === 'delete_category') {
        $category_id = $_POST['category_id'];
        if (empty($category_id)) {
            $_SESSION['errors'] = ['Category ID is required.'];
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$category_id])) {
                $_SESSION['success'] = 'Category deleted successfully!';
            } else {
                $_SESSION['errors'] = ['Failed to delete category.'];
            }
        }
    }

    header("Location: categories.php");
    exit();
}

// --- Fetch Page Data ---
try {
    $categories = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Question Categories</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal"><i class="fas fa-plus me-2"></i>Create New Category</button>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Categories</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light"><tr><th>ID</th><th>Category Name</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="createCategoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="categories.php" method="POST"><div class="modal-body"><input type="hidden" name="action" value="create_category"><div class="mb-3"><label for="create_category_name" class="form-label">Category Name</label><input type="text" class="form-control" id="create_category_name" name="category_name" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Create</button></div></form></div></div></div>
<div class="modal fade" id="editCategoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="categories.php" method="POST"><div class="modal-body"><input type="hidden" name="action" value="update_category"><input type="hidden" name="category_id" id="edit_category_id"><div class="mb-3"><label for="edit_category_name" class="form-label">Category Name</label><input type="text" class="form-control" id="edit_category_name" name="category_name" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>
<div class="modal fade" id="deleteCategoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="categories.php" method="POST"><div class="modal-body"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" id="delete_category_id"><p>Are you sure you want to delete the category: <strong id="delete_category_name"></strong>? This cannot be undone.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete</button></div></form></div></div></div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // For Edit Modal
    var editModal = document.getElementById('editCategoryModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var catId = button.getAttribute('data-id');
        var catName = button.getAttribute('data-name');
        editModal.querySelector('#edit_category_id').value = catId;
        editModal.querySelector('#edit_category_name').value = catName;
    });

    // For Delete Modal
    var deleteModal = document.getElementById('deleteCategoryModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var catId = button.getAttribute('data-id');
        var catName = button.getAttribute('data-name');
        deleteModal.querySelector('#delete_category_id').value = catId;
        deleteModal.querySelector('#delete_category_name').textContent = catName;
    });
});
</script>
