<?php
$pageTitle = "Live Test Monitoring";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch all in-progress test attempts ---
$sql = "SELECT
            ta.attempt_id,
            ta.start_time,
            t.title as test_title,
            t.time_limit_minutes,
            u.first_name,
            u.last_name
        FROM test_attempts ta
        JOIN tests t ON ta.test_id = t.test_id
        JOIN users u ON ta.user_id = u.user_id
        WHERE ta.status = 'in_progress'
        ORDER BY ta.start_time ASC";
$result = $conn->query($sql);
$live_attempts = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Live Test Monitoring</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <h3 class="fs-4 mb-3">Ongoing Test Sessions</h3>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Test Title</th>
                                    <th>Start Time</th>
                                    <th>Time Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($live_attempts)): ?>
                                    <tr><td colspan="4" class="text-center">There are no active test sessions right now.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($live_attempts as $attempt): ?>
                                        <tr class="live-attempt-row"
                                            data-start-time="<?php echo $attempt['start_time']; ?>"
                                            data-limit-minutes="<?php echo $attempt['time_limit_minutes']; ?>">
                                            <td><?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['test_title']); ?></td>
                                            <td><?php echo date('M d, Y, g:i A', strtotime($attempt['start_time'])); ?></td>
                                            <td class="timer-cell">--:--</td>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const attemptRows = document.querySelectorAll('.live-attempt-row');

    attemptRows.forEach(row => {
        const startTime = new Date(row.dataset.startTime).getTime();
        const timeLimitMinutes = parseInt(row.dataset.limitMinutes, 10);
        const timerCell = row.querySelector('.timer-cell');

        if (isNaN(timeLimitMinutes) || timeLimitMinutes <= 0) {
            timerCell.innerHTML = 'No time limit';
            return;
        }

        const endTime = startTime + timeLimitMinutes * 60 * 1000;

        const updateTimer = () => {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                timerCell.innerHTML = "<span class='text-danger'>Time Expired</span>";
                // Optionally remove the row or stop the interval
                // clearInterval(interval);
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timerText = ('0' + minutes).slice(-2) + ":" + ('0' + seconds).slice(-2);
            if (hours > 0) {
                timerText = ('0' + hours).slice(-2) + ":" + timerText;
            }
            timerCell.innerHTML = timerText;
        };

        updateTimer(); // Initial call
        const interval = setInterval(updateTimer, 1000); // Update every second
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
