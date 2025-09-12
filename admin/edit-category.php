<?php
$pageTitle = "Edit Category";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get Category ID and Fetch Data ---
$category_id_to_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$category_id_to_edit) {
    header("Location: question-categories.php?error=invalidid");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM question_categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id_to_edit);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    header("Location: question-categories.php?error=cat_not_found");
    exit;
}

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($category_name)) {
        $errors[] = "Category name is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE question_categories SET category_name = ?, description = ? WHERE category_id = ?");
        $stmt->bind_param("ssi", $category_name, $description, $category_id_to_edit);

        if ($stmt->execute()) {
            header("Location: question-categories.php?success=cat_updated");
            exit;
        } else {
            $errors[] = "Failed to update category. Please try again.";
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
                <h2 class="fs-2 m-0">Edit Category</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Editing Category: <?php echo htmlspecialchars($category['category_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="edit-category.php?id=<?php echo $category_id_to_edit; ?>" method="POST">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Update Category</button>
                                    <a href="question-categories.php" class="btn btn-secondary">Cancel</a>
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
