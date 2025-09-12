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

// --- Get Test ID and Fetch Data ---
$test_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$test_id) {
    header("Location: results.php?error=invalidid");
    exit;
}

// Fetch test details
$test = $conn->query("SELECT * FROM tests WHERE test_id = $test_id")->fetch_assoc();
if (!$test) {
    header("Location: results.php?error=notfound");
    exit;
}

// Fetch all completed attempts for this test
$sql = "SELECT
            ta.attempt_id,
            ta.score,
            ta.end_time,
            u.first_name,
            u.last_name,
            u.email
        FROM test_attempts ta
        JOIN users u ON ta.user_id = u.user_id
        WHERE ta.test_id = ? AND ta.status = 'completed'
        ORDER BY ta.score DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $test_id);
$stmt->execute();
$attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate summary stats
$total_attempts = count($attempts);
$average_score = ($total_attempts > 0) ? array_sum(array_column($attempts, 'score')) / $total_attempts : 0;


require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Test Results</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col">
                    <h3 class="fs-4 mb-3">Results for: <?php echo htmlspecialchars($test['title']); ?></h3>
                    <a href="results.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to All Tests</a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 my-3">
                <div class="col-md-6">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_attempts; ?></h3>
                            <p class="fs-5 text-muted mb-0">Total Attempts</p>
                        </div>
                        <i class="bi bi-card-checklist fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo number_format($average_score, 1); ?>%</h3>
                            <p class="fs-5 text-muted mb-0">Average Score</p>
                        </div>
                        <i class="bi bi-graph-up fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
            </div>

            <!-- Detailed Results Table -->
            <div class="row my-5">
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Individual Student Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>Date Completed</th>
                                            <th>Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($attempts)): ?>
                                            <tr><td colspan="4" class="text-center">No completed attempts for this test yet.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($attempts as $attempt): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                                    <td><?php echo date('M d, Y, g:i A', strtotime($attempt['end_time'])); ?></td>
                                                    <td><strong><?php echo number_format($attempt['score'], 2); ?>%</strong></td>
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
