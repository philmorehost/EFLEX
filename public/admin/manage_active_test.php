<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control & Helpers ---
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

$user_test_id = $_GET['id'] ?? null;
if (!$user_test_id || !filter_var($user_test_id, FILTER_VALIDATE_INT)) {
    $_SESSION['errors'] = ["Invalid active test ID."];
    header("Location: live_exams.php");
    exit();
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Add Extra Time ---
    if ($action === 'add_time') {
        $extra_minutes = filter_var($_POST['extra_minutes'] ?? 0, FILTER_VALIDATE_INT);
        if ($extra_minutes > 0) {
            $sql = "UPDATE user_tests SET extra_time_added = extra_time_added + ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$extra_minutes, $user_test_id])) {
                $admin_id = $_SESSION['user_id'];
                $log_stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, test_id, action, details) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$admin_id, $user_test_id, 'ADD_TIME', "Added $extra_minutes minutes."]);
                $_SESSION['success'] = "Successfully added $extra_minutes minutes to the test.";
            } else {
                $_SESSION['errors'] = ["Failed to add extra time."];
            }
        } else {
            $_SESSION['errors'] = ["Please enter a valid number of minutes."];
        }
    }

    // --- Stop Exam ---
    if ($action === 'stop_exam') {
        $stop_reason = trim($_POST['stop_reason'] ?? '');
        if (!empty($stop_reason)) {
            $sql = "UPDATE user_tests SET status = 'stopped', stop_reason = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$stop_reason, $user_test_id])) {
                $admin_id = $_SESSION['user_id'];
                $log_stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, test_id, action, details) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$admin_id, $user_test_id, 'STOP_EXAM', $stop_reason]);
                $_SESSION['success'] = "The exam has been stopped successfully.";
                header("Location: live_exams.php"); // Redirect back to the list after stopping
                exit();
            } else {
                $_SESSION['errors'] = ["Failed to stop the exam."];
            }
        } else {
            $_SESSION['errors'] = ["A reason is required to stop the exam."];
        }
    }

    header("Location: manage_active_test.php?id=" . $user_test_id);
    exit();
}


// --- Fetch Page Data ---
try {
    $sql = "SELECT ut.id, ut.start_time, ut.extra_time_added, t.test_name, t.duration, u.name as student_name
            FROM user_tests ut
            JOIN tests t ON ut.test_id = t.id
            JOIN users u ON ut.user_id = u.id
            WHERE ut.id = ? AND ut.status = 'in_progress'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_test_id]);
    $active_test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$active_test) {
        $_SESSION['errors'] = ["This test session is no longer active or does not exist."];
        header("Location: live_exams.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manage Active Test</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Test Details</h6></div>
                <div class="card-body">
                    <p><strong>Student:</strong> <?php echo htmlspecialchars($active_test['student_name']); ?></p>
                    <p><strong>Test:</strong> <?php echo htmlspecialchars($active_test['test_name']); ?></p>
                    <p><strong>Start Time:</strong> <?php echo htmlspecialchars($active_test['start_time']); ?></p>
                    <p><strong>Base Duration:</strong> <?php echo htmlspecialchars($active_test['duration']); ?> minutes</p>
                    <p><strong>Extra Time Added:</strong> <?php echo htmlspecialchars($active_test['extra_time_added']); ?> minutes</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Live Controls</h6></div>
                <div class="card-body">
                    <form action="manage_active_test.php?id=<?php echo $user_test_id; ?>" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add_time">
                        <div class="mb-3">
                            <label for="extra_minutes" class="form-label">Add Extra Time (minutes)</label>
                            <input type="number" class="form-control" name="extra_minutes" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-info">Add Time</button>
                    </form>
                    <hr>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#stopExamModal">
                        Stop Exam
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stop Exam Modal -->
<div class="modal fade" id="stopExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Confirm Stop Exam</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="manage_active_test.php?id=<?php echo $user_test_id; ?>" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to stop this exam? This action cannot be undone.</p>
                    <input type="hidden" name="action" value="stop_exam">
                    <div class="mb-3">
                        <label for="stop_reason" class="form-label">Reason for Stopping</label>
                        <textarea class="form-control" name="stop_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Stop Exam Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
