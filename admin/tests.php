<?php
$pageTitle = "Test Management";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch all tests from the database ---
$sql = "SELECT
            t.test_id,
            t.title,
            t.time_limit_minutes,
            u.first_name,
            u.last_name,
            (SELECT COUNT(*) FROM test_questions WHERE test_id = t.test_id) as question_count
        FROM tests t
        LEFT JOIN users u ON t.created_by = u.user_id
        ORDER BY t.test_id DESC";
$result = $conn->query($sql);
$tests = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Test Management</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fs-4 mb-0">All Tests</h3>
                        <a href="create-test.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Create New Test
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Questions</th>
                                    <th scope="col">Time Limit</th>
                                    <th scope="col">Created By</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tests)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No tests have been created yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tests as $test): ?>
                                        <tr>
                                            <th scope="row"><?php echo $test['test_id']; ?></th>
                                            <td><?php echo htmlspecialchars($test['title']); ?></td>
                                            <td><?php echo $test['question_count']; ?></td>
                                            <td><?php echo $test['time_limit_minutes'] ? $test['time_limit_minutes'] . ' mins' : 'None'; ?></td>
                                            <td><?php echo htmlspecialchars($test['first_name'] . ' ' . $test['last_name']); ?></td>
                                            <td>
                                                <a href="build-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-sm btn-success">Build</a>
                                                <a href="edit-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">Delete</a>
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
