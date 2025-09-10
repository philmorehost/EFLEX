<?php
session_start();
require_once __DIR__ . '/../templates/header.php'; // Using main header for now

$pdo = require __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? '';
$user_test_id = $_GET['id'] ?? null;

if (!$user_test_id || !filter_var($user_test_id, FILTER_VALIDATE_INT)) {
    die("Invalid result ID.");
}

// --- Fetch Result Data and Verify Access ---
try {
    $stmt = $pdo->prepare(
        "SELECT ut.*, t.test_name, u.name as student_name
         FROM user_tests ut
         JOIN tests t ON ut.test_id = t.id
         JOIN users u ON ut.user_id = u.id
         WHERE ut.id = ?"
    );
    $stmt->execute([$user_test_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Access Control: Allow if user is the student who took the test, or an admin/super admin.
    if (!$result || ($result['user_id'] != $user_id && !in_array($user_role, ['Super Admin', 'Admin']))) {
        die("Access Denied: You do not have permission to view these results.");
    }

    // Fetch detailed answers
    $sql = "SELECT q.question_text, q.correct_answer, ua.selected_answer, ua.is_correct
            FROM user_answers ua
            JOIN questions q ON ua.question_id = q.id
            WHERE ua.user_test_id = ?
            ORDER BY q.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_test_id]);
    $answer_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .printable-area, .printable-area * {
            visibility: visible;
        }
        .printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none;
        }
    }
</style>

<div class="container-fluid">
    <div class="printable-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Test Results</h1>
            <button onclick="window.print();" class="btn btn-info no-print"><i class="fas fa-print me-2"></i>Print Results</button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Test Name:</strong> <?php echo htmlspecialchars($result['test_name']); ?></div>
                    <div class="col-md-4"><strong>Student:</strong> <?php echo htmlspecialchars($result['student_name']); ?></div>
                    <div class="col-md-4"><strong>Final Score:</strong> <span class="fw-bold fs-5 text-success"><?php echo number_format($result['score'], 2); ?>%</span></div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Answer Breakdown</h6>
            </div>
            <div class="card-body">
                <?php foreach ($answer_details as $index => $detail): ?>
                    <div class="mb-4 pb-2 border-bottom">
                        <p><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo $detail['question_text']; ?></p>
                        <p>Your Answer: <span class="<?php echo $detail['is_correct'] ? 'text-success' : 'text-danger'; ?>"><?php echo $detail['selected_answer']; ?></span></p>
                        <?php if (!$detail['is_correct']): ?>
                            <p>Correct Answer: <span class="text-success"><?php echo $detail['correct_answer']; ?></span></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
