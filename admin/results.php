<?php
$pageTitle = "Test Results";
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

// --- Fetch all tests with attempt counts ---
$sql = "SELECT
            t.test_id,
            t.title,
            (SELECT COUNT(*) FROM test_questions WHERE test_id = t.test_id) as question_count,
            (SELECT COUNT(*) FROM test_attempts WHERE test_id = t.test_id AND status = 'completed') as attempt_count
        FROM tests t
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
                <h2 class="fs-2 m-0">Results & Analytics</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <h3 class="fs-4 mb-3">Select a Test to View Results</h3>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Test Title</th>
                                    <th scope="col">Questions</th>
                                    <th scope="col">Completed Attempts</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tests)): ?>
                                    <tr><td colspan="5" class="text-center">No tests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($tests as $test): ?>
                                        <tr>
                                            <th scope="row"><?php echo $test['test_id']; ?></th>
                                            <td><?php echo htmlspecialchars($test['title']); ?></td>
                                            <td><?php echo $test['question_count']; ?></td>
                                            <td><?php echo $test['attempt_count']; ?></td>
                                            <td>
                                                <a href="test-results.php?id=<?php echo $test['test_id']; ?>" class="btn btn-sm btn-primary">View Results</a>
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
