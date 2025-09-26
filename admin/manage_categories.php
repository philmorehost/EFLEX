<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();
protect_admin_page();

$errors = [];
$success = null;
$edit_category = null;

// Handle form submissions (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (empty($name) || empty($slug)) {
        $errors[] = 'Category name and slug are required.';
    }

    if (empty($errors)) {
        if ($id) { // Update existing category
            $stmt = $mysqli->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $slug, $id);
            $success = "Category updated successfully.";
        } else { // Insert new category
            $stmt = $mysqli->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $slug);
            $success = "Category added successfully.";
        }

        if (!$stmt->execute()) {
            $errors[] = 'Database error: ' . $stmt->error;
            $success = null;
        }
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    redirect('manage_categories.php');
}

// Handle edit action (fetch data for the form)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_category = $result->fetch_assoc();
}


// Fetch all categories
$categories = $mysqli->query("SELECT * FROM categories ORDER BY name ASC");

require_once 'partials/admin_header.php';
?>

<h1 class="h3 mb-2 text-gray-800">Manage Categories</h1>
<p class="mb-4">Add, edit, or delete product categories.</p>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h6>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?><p><?php echo $error; ?></p><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="manage_categories.php">
                    <input type="hidden" name="id" value="<?php echo $edit_category['id'] ?? ''; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_category['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($edit_category['slug'] ?? ''); ?>" required>
                        <small class="form-text text-muted">A unique, URL-friendly identifier (e.g., "php-scripts").</small>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $edit_category ? 'Update' : 'Add'; ?> Category</button>
                    <?php if ($edit_category): ?>
                        <a href="manage_categories.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Existing Categories</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                    <td>
                                        <a href="manage_categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                        <a href="manage_categories.php?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'partials/admin_footer.php';
?>