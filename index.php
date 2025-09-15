<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// --- Role-based routing ---
if (isset($_SESSION['user_id'])) {
    $role_id = $_SESSION['role_id'];

    // If user is an Admin, Super Admin, or Staff, redirect to admin dashboard
    if (in_array($role_id, [1, 2, 3])) {
        header("Location: admin/index.php");
        exit;
    }

    // If user is a regular User, display the student dashboard
    if ($role_id == 4) {
        $user_id = $_SESSION['user_id'];

        // Fetch all available tests and join with attempts to see if user has taken them
        $sql = "SELECT
                    t.test_id, t.title, t.description, t.time_limit_minutes,
                    (SELECT COUNT(*) FROM test_questions WHERE test_id = t.test_id) as question_count,
                    ta.score, ta.status
                FROM tests t
                LEFT JOIN test_attempts ta ON t.test_id = ta.test_id AND ta.user_id = ?
                ORDER BY t.test_id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
?>
        <!-- Student Dashboard HTML -->
        <div class="container mt-5">
            <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <h3 class="mb-4">Available Tests</h3>
            <div class="row">
                <?php if (empty($tests)): ?>
                    <div class="col">
                        <p>There are no tests available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tests as $test): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($test['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($test['description']); ?></p>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li><strong>Questions:</strong> <?php echo $test['question_count']; ?></li>
                                        <li><strong>Time Limit:</strong> <?php echo $test['time_limit_minutes']; ?> minutes</li>
                                    </ul>
                                    <div class="mt-auto">
                                        <?php if ($test['status'] === 'completed'): ?>
                                            <p class="mb-1"><strong>Your Score:</strong> <?php echo number_format($test['score'], 2); ?>%</p>
                                            <a href="#" class="btn btn-secondary disabled w-100">Test Completed</a>
                                        <?php elseif ($test['status'] === 'in_progress'): ?>
                                            <a href="take-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-warning w-100">Continue Test</a>
                                        <?php else: ?>
                                            <a href="take-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-primary w-100">Start Test</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
             <a href="logout.php" class="btn btn-danger mt-4">Logout</a>
        </div>
<?php
    }
} else {
    // --- Public Welcome Page (if not logged in) ---
?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="card-title display-4">Welcome to <?php echo SITE_NAME; ?></h1>
                        <p class="card-text lead">Your reliable and user-friendly platform for computer-based testing.</p>
                        <hr class="my-4">
                        <p>Please log in to continue or register for a new account.</p>
                        <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                        <a href="register.php" class="btn btn-secondary btn-lg">Register</a>
                    </div>
                </div>
                <div class="mt-4">
                    <p><small><a href="<?php echo BASE_URL; ?>admin/" class="text-muted">Admin Panel</a></small></p>
                </div>
            </div>
        </div>
    </div>
<?php
}

require_once __DIR__ . '/includes/footer.php';
?>
