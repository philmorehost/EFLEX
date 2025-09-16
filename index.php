<?php
// Check if the config file exists. If not, redirect to the installer.
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: install/');
    exit;
}

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

        // --- Fetch user data ---
        $user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();
        $user_stmt->close();

        $profile_pic = $user['profile_picture_path'] ?? 'assets/img/default_avatar.svg';
        if (empty($user['profile_picture_path']) || !file_exists(__DIR__ . '/' . $user['profile_picture_path'])) {
            $profile_pic = 'assets/img/default_avatar.svg';
        }

        // --- Fetch Student Statistics ---
        $stats_sql = "SELECT
                        COUNT(*) as total_completed,
                        AVG(score) as average_score,
                        SUM(CASE WHEN score >= 70 THEN 1 ELSE 0 END) as tests_passed
                    FROM test_attempts
                    WHERE user_id = ? AND status = 'completed'";
        $stats_stmt = $conn->prepare($stats_sql);
        $stats_stmt->bind_param("i", $user_id);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
        $stats_stmt->close();

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
            <div class="d-flex align-items-center mb-4">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="img-thumbnail rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                <h1 class="mb-0">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            </div>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card text-center text-white bg-primary shadow">
                        <div class="card-body">
                            <i class="fas fa-file-alt fa-3x mb-2"></i>
                            <h3 class="card-title"><?php echo $stats['total_completed'] ?? 0; ?></h3>
                            <p class="card-text">Tests Taken</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center text-white bg-success shadow">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-3x mb-2"></i>
                            <h3 class="card-title"><?php echo number_format($stats['average_score'] ?? 0, 1); ?>%</h3>
                            <p class="card-text">Average Score</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center text-white bg-info shadow">
                        <div class="card-body">
                            <i class="fas fa-trophy fa-3x mb-2"></i>
                            <h3 class="card-title"><?php echo $stats['tests_passed'] ?? 0; ?></h3>
                            <p class="card-text">Tests Passed</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mb-4">Available Tests</h3>
            <div class="row">
                <?php if (empty($tests)): ?>
                    <div class="col">
                        <p>There are no tests available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tests as $test): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($test['title']); ?></h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($test['description']); ?></p>
                                    <ul class="list-unstyled mt-3 mb-4">
                                        <li><strong>Questions:</strong> <?php echo $test['question_count']; ?></li>
                                        <li><strong>Time Limit:</strong> <?php echo $test['time_limit_minutes']; ?> minutes</li>
                                    </ul>
                                    <div class="mt-auto text-center">
                                        <?php if ($test['status'] === 'completed'): ?>
                                            <p class="mb-2"><strong>Your Score:</strong> <span class="badge bg-success"><?php echo number_format($test['score'], 2); ?>%</span></p>
                                            <a href="#" class="btn btn-light disabled w-100"><i class="fas fa-check-circle"></i> Test Completed</a>
                                        <?php elseif ($test['status'] === 'in_progress'): ?>
                                            <a href="take-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-warning w-100"><i class="fas fa-arrow-right"></i> Continue Test</a>
                                        <?php else: ?>
                                            <a href="take-test.php?id=<?php echo $test['test_id']; ?>" class="btn btn-primary w-100"><i class="fas fa-play"></i> Start Test</a>
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
