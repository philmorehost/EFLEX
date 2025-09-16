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

// --- Fetch tests from the database ---
$search_term = '';
$sql = "SELECT
            t.test_id,
            t.title,
            t.time_limit_minutes,
            u.first_name,
            u.last_name,
            (SELECT COUNT(*) FROM test_questions WHERE test_id = t.test_id) as question_count
        FROM tests t
        LEFT JOIN users u ON t.created_by = u.user_id";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $sql .= " WHERE t.title LIKE ?";
    $sql .= " ORDER BY t.test_id DESC";
    $stmt = $conn->prepare($sql);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("s", $like_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY t.test_id DESC";
    $result = $conn->query($sql);
}
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

                    <!-- Search and Filter Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form action="tests.php" method="get" class="row g-3 align-items-center">
                                <div class="col-md-8">
                                    <label for="search" class="visually-hidden">Search</label>
                                    <input type="search" class="form-control" id="search" name="search" placeholder="Search by test title..." value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                                <div class="col-md-2">
                                     <a href="tests.php" class="btn btn-outline-secondary w-100">Clear</a>
                                </div>
                            </form>
                        </div>
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
