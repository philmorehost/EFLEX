<?php
$pageTitle = "Question Categories";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Admins and Staff can manage categories
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch all categories with question counts ---
$sql = "SELECT
            qc.category_id,
            qc.category_name,
            qc.description,
            (SELECT COUNT(*) FROM questions WHERE category_id = qc.category_id) as question_count
        FROM question_categories qc
        ORDER BY qc.category_name ASC";
$result = $conn->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Question Categories</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fs-4 mb-0">All Categories</h3>
                        <a href="add-category.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Add New Category
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Questions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="5" class="text-center">No categories found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?php echo $cat['category_id']; ?></td>
                                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                            <td><?php echo $cat['question_count']; ?></td>
                                            <td>
                                                <a href="edit-category.php?id=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete-category.php?id=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? Deleting a category will also delete all questions within it.');">Delete</a>
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
