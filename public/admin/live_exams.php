<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Fetch Page Data ---
try {
    $sql = "SELECT ut.id, ut.start_time, t.test_name, t.duration, u.name as student_name
            FROM user_tests ut
            JOIN tests t ON ut.test_id = t.id
            JOIN users u ON ut.user_id = u.id
            WHERE ut.status = 'in_progress'
            ORDER BY ut.start_time ASC";
    $live_tests = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Live Exam Monitoring</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Active Test Sessions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Test Name</th>
                            <th>Start Time</th>
                            <th>Time Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($live_tests)): ?>
                            <tr>
                                <td colspan="5" class="text-center">There are no active test sessions at the moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($live_tests as $test):
                                $start_timestamp = strtotime($test['start_time']);
                                $duration_seconds = $test['duration'] * 60;
                                $end_timestamp = $start_timestamp + $duration_seconds;
                                $time_remaining_seconds = $end_timestamp - time();
                                $time_remaining_formatted = $time_remaining_seconds > 0 ? gmdate("H:i:s", $time_remaining_seconds) : 'Ended';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($test['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', $start_timestamp); ?></td>
                                    <td><?php echo $time_remaining_formatted; ?></td>
                                    <td>
                                        <a href="manage_active_test.php?id=<?php echo $test['id']; ?>" class="btn btn-sm btn-primary">Manage</a>
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

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
