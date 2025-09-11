<?php
$pageTitle = "Admin Dashboard";
// Use __DIR__ to ensure the path is correct
require_once __DIR__ . '/../includes/config.php';

// --- Authentication Check ---
// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to the login page.
    header("Location: ../login.php?error=authrequired");
    exit;
}

// --- Role Check ---
// Check if the user has an allowed role (Super Admin, Admin, or Staff).
// Role IDs: 1 = Super Admin, 2 = Admin, 3 = Staff
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    // If the user's role is not allowed, destroy the session and redirect.
    session_destroy();
    header("Location: ../login.php?error=accessdenied");
    exit;
}

// --- Fetch Dashboard Statistics ---
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_tests = $conn->query("SELECT COUNT(*) as count FROM tests")->fetch_assoc()['count'];
$total_questions = $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count'];
$completed_attempts = $conn->query("SELECT COUNT(*) as count FROM test_attempts WHERE status = 'completed'")->fetch_assoc()['count'];

// --- Fetch Recent Activities ---
$recent_users = $conn->query("SELECT first_name, last_name, email, created_at FROM users ORDER BY user_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_attempts = $conn->query("SELECT t.title, u.first_name, u.last_name, ta.score, ta.end_time
                                 FROM test_attempts ta
                                 JOIN tests t ON ta.test_id = t.test_id
                                 JOIN users u ON ta.user_id = u.user_id
                                 WHERE ta.status = 'completed'
                                 ORDER BY ta.attempt_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);


require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Dashboard</h2>
            </div>

            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <h3 class="fs-4 mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h3>
            <div class="row g-4 my-3">
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_users; ?></h3>
                            <p class="fs-5 text-muted mb-0">Total Users</p>
                        </div>
                        <i class="bi bi-people fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_tests; ?></h3>
                            <p class="fs-5 text-muted mb-0">Total Tests</p>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_questions; ?></h3>
                            <p class="fs-5 text-muted mb-0">Questions</p>
                        </div>
                        <i class="bi bi-patch-question fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $completed_attempts; ?></h3>
                            <p class="fs-5 text-muted mb-0">Tests Taken</p>
                        </div>
                        <i class="bi bi-check2-circle fs-1 primary-text border rounded-pill p-3"></i>
                    </div>
                </div>
            </div>

            <div class="row g-4 my-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Recent Registrations</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($recent_users)): ?>
                                    <li class="list-group-item">No recent registrations.</li>
                                <?php else: ?>
                                    <?php foreach($recent_users as $user): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </div>
                                            <span class="badge bg-light text-dark rounded-pill"><?php echo date('M d', strtotime($user['created_at'])); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                     <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Recent Test Attempts</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($recent_attempts)): ?>
                                    <li class="list-group-item">No recent test attempts.</li>
                                <?php else: ?>
                                    <?php foreach($recent_attempts as $attempt): ?>
                                         <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?></div>
                                                <span class="text-muted"><?php echo htmlspecialchars($attempt['title']); ?></span>
                                            </div>
                                            <span class="badge bg-primary rounded-pill"><?php echo number_format($attempt['score'], 1); ?>%</span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- /#page-content-wrapper -->

<script>
    // JS for sidebar toggle
    document.getElementById("menu-toggle").addEventListener("click", function() {
        document.getElementById("admin-wrapper").classList.toggle("toggled");
    });
</script>

<?php
// Use __DIR__ to ensure the path is correct
require_once __DIR__ . '/../includes/footer.php';
?>
