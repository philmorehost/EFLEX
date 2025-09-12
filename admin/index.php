<?php
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
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
$recent_attempts = $conn->query("SELECT t.title, u.first_name, u.last_name, ta.score FROM test_attempts ta JOIN tests t ON ta.test_id = t.test_id JOIN users u ON ta.user_id = u.user_id WHERE ta.status = 'completed' ORDER BY ta.attempt_id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// --- Fetch Chart Data ---
// Pass/Fail Data
$pass_fail_sql = "SELECT
                    SUM(CASE WHEN ta.score >= t.passing_score THEN 1 ELSE 0 END) as passed,
                    SUM(CASE WHEN ta.score < t.passing_score THEN 1 ELSE 0 END) as failed
                  FROM test_attempts ta
                  JOIN tests t ON ta.test_id = t.test_id
                  WHERE ta.status = 'completed'";
$pass_fail_data = $conn->query($pass_fail_sql)->fetch_assoc();

// Score Distribution Data
$score_dist_sql = "SELECT floor(score/10)*10 as score_bracket, COUNT(*) as count
                   FROM test_attempts
                   WHERE status = 'completed'
                   GROUP BY score_bracket
                   ORDER BY score_bracket ASC";
$score_dist_result = $conn->query($score_dist_sql);
$score_dist_data = [];
while($row = $score_dist_result->fetch_assoc()) {
    $score_dist_data[$row['score_bracket']] = $row['count'];
}


require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
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

            <!-- Stat Cards -->
            <div class="row g-4 my-3">
                 <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div><h3 class="fs-2"><?php echo $total_users; ?></h3><p class="fs-5 text-muted mb-0">Total Users</p></div>
                        <i class="bi bi-people fs-1 card-icon bg-info-light"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div><h3 class="fs-2"><?php echo $total_tests; ?></h3><p class="fs-5 text-muted mb-0">Total Tests</p></div>
                        <i class="bi bi-file-earmark-text fs-1 card-icon bg-success-light"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div><h3 class="fs-2"><?php echo $total_questions; ?></h3><p class="fs-5 text-muted mb-0">Questions</p></div>
                        <i class="bi bi-patch-question fs-1 card-icon bg-warning-light"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div><h3 class="fs-2"><?php echo $completed_attempts; ?></h3><p class="fs-5 text-muted mb-0">Tests Taken</p></div>
                        <i class="bi bi-check2-circle fs-1 card-icon bg-danger-light"></i>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row g-4 my-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0">Pass/Fail Rate</h5></div>
                        <div class="card-body"><canvas id="passFailChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0">Score Distribution</h5></div>
                        <div class="card-body"><canvas id="scoreDistributionChart"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row g-4 my-3">
                <!-- (Recent Activity HTML remains the same) -->
                 <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Recent Registrations</h5></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach($recent_users as $user): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                        <span class="badge bg-light text-dark rounded-pill"><?php echo date('M d', strtotime($user['created_at'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                     <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Recent Test Attempts</h5></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach($recent_attempts as $attempt): ?>
                                     <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold"><?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?></div>
                                            <span class="text-muted"><?php echo htmlspecialchars($attempt['title']); ?></span>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo number_format($attempt['score'], 1); ?>%</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pass/Fail Chart
    const passFailCtx = document.getElementById('passFailChart');
    if (passFailCtx) {
        new Chart(passFailCtx, {
            type: 'pie',
            data: {
                labels: ['Passed', 'Failed'],
                datasets: [{
                    label: 'Test Outcomes',
                    data: [<?php echo $pass_fail_data['passed'] ?? 0; ?>, <?php echo $pass_fail_data['failed'] ?? 0; ?>],
                    backgroundColor: ['rgba(25, 135, 84, 0.7)', 'rgba(220, 53, 69, 0.7)'],
                    borderColor: ['rgba(25, 135, 84, 1)', 'rgba(220, 53, 69, 1)'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Score Distribution Chart
    const scoreDistCtx = document.getElementById('scoreDistributionChart');
    if (scoreDistCtx) {
        const scoreLabels = ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80-89', '90-100'];
        const scoreData = [];
        for (let i = 0; i <= 90; i += 10) {
            scoreData.push(<?php echo json_encode($score_dist_data); ?>[i] || 0);
        }

        new Chart(scoreDistCtx, {
            type: 'bar',
            data: {
                labels: scoreLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: scoreData,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
