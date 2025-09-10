<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'schedule_test') {
    $test_id = $_POST['test_id'];
    $scheduled_time = $_POST['scheduled_time'];

    if (empty($test_id) || empty($scheduled_time)) {
        $_SESSION['errors'] = ['Test and schedule time are required.'];
    } else {
        // Here you might want to check if a schedule for this test already exists and handle it (e.g., update or prevent).
        // For simplicity, we'll allow multiple schedules for now.
        $stmt = $pdo->prepare("INSERT INTO exam_schedules (test_id, scheduled_time, status) VALUES (?, ?, 'pending')");
        if ($stmt->execute([$test_id, $scheduled_time])) {
            $_SESSION['success'] = 'Test scheduled successfully!';
        } else {
            $_SESSION['errors'] = ['Failed to schedule test.'];
        }
    }
    header("Location: manage_tests.php");
    exit();
}


// --- Fetch Page Data ---
try {
    // Fetch all tests and their most recent schedule time, if any.
    $sql = "SELECT t.id, t.test_name, t.duration, (SELECT es.scheduled_time FROM exam_schedules es WHERE es.test_id = t.id ORDER BY es.scheduled_time DESC LIMIT 1) as scheduled_time
            FROM tests t
            ORDER BY t.id DESC";
    $tests = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Tests</h1>
        <a href="<?php echo BASE_URL; ?>/admin/create_test.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create New Test</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Created Tests</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Test Name</th><th>Duration</th><th>Last Scheduled For</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($test['id']); ?></td>
                                <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                <td><?php echo htmlspecialchars($test['duration']); ?> mins</td>
                                <td><?php echo $test['scheduled_time'] ? date('Y-m-d H:i', strtotime($test['scheduled_time'])) : 'Not scheduled'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info schedule-btn" data-bs-toggle="modal" data-bs-target="#scheduleTestModal" data-id="<?php echo $test['id']; ?>" data-name="<?php echo htmlspecialchars($test['test_name']); ?>"><i class="fas fa-clock"></i> Schedule</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Test Modal -->
<div class="modal fade" id="scheduleTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Schedule Test: <span id="scheduleTestName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="manage_tests.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="schedule_test">
                    <input type="hidden" name="test_id" id="schedule_test_id">
                    <div class="mb-3">
                        <label for="scheduled_time" class="form-label">Date and Time</label>
                        <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Set Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var scheduleModal = document.getElementById('scheduleTestModal');
    scheduleModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var testId = button.getAttribute('data-id');
        var testName = button.getAttribute('data-name');
        scheduleModal.querySelector('#schedule_test_id').value = testId;
        scheduleModal.querySelector('#scheduleTestName').textContent = testName;
    });
});
</script>
