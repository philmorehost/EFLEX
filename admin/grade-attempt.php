<?php
$pageTitle = "Grade Test Attempt";
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/lib/htmlpurifier/library/HTMLPurifier.standalone.php';

// --- HTML Purifier Setup ---
$purifier_config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($purifier_config);

// --- Auth Check & Role Check ---
// (Assuming this is an admin-only page)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$allowed_roles = [1, 2]; // Super Admin, Admin
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

$attempt_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$attempt_id) {
    header("Location: results.php?error=invalidid");
    exit;
}

// --- Handle Grading Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade'], $_POST['question_id'])) {
    $grade = (int)$_POST['grade'];
    $question_id_to_grade = (int)$_POST['question_id'];

    if ($grade === 0 || $grade === 1) {
        // Find the specific student_answer record to update
        $update_sql = "UPDATE student_answers SET score = ? WHERE attempt_id = ? AND question_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $grade, $attempt_id, $question_id_to_grade);
        $update_stmt->execute();
        $update_stmt->close();

        // --- Recalculate total score ---
        $recalc_sql = "SELECT sa.selected_option_id, sa.score as sa_score, o.is_correct
                       FROM student_answers sa
                       LEFT JOIN options o ON sa.selected_option_id = o.option_id
                       WHERE sa.attempt_id = ?";
        $recalc_stmt = $conn->prepare($recalc_sql);
        $recalc_stmt->bind_param("i", $attempt_id);
        $recalc_stmt->execute();
        $all_answers = $recalc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $recalc_stmt->close();

        $total_questions_in_test = count($all_answers);
        $correct_count = 0;
        foreach ($all_answers as $ans) {
            if ($ans['sa_score'] === 1) { // Manually graded as correct
                $correct_count++;
            } elseif ($ans['is_correct'] === 1) { // Auto-graded MCQ/TF
                $correct_count++;
            }
        }

        $new_score = ($total_questions_in_test > 0) ? ($correct_count / $total_questions_in_test) * 100 : 0;

        $score_update_stmt = $conn->prepare("UPDATE test_attempts SET score = ? WHERE attempt_id = ?");
        $score_update_stmt->bind_param("di", $new_score, $attempt_id);
        $score_update_stmt->execute();
        $score_update_stmt->close();
    }
}


// --- Fetch Attempt Details ---
$sql = "SELECT ta.*, t.title as test_title, u.first_name, u.last_name, u.email
        FROM test_attempts ta
        JOIN tests t ON ta.test_id = t.test_id
        JOIN users u ON ta.user_id = u.user_id
        WHERE ta.attempt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$attempt) {
    header("Location: results.php?error=notfound");
    exit;
}

// --- Fetch Questions and Student Answers ---
$sql_qa = "SELECT q.question_id, q.question_text, q.question_type, q.model_answer, sa.answer_text, sa.selected_option_id, sa.score as answer_score
           FROM questions q
           JOIN test_questions tq ON q.question_id = tq.question_id
           LEFT JOIN student_answers sa ON q.question_id = sa.question_id AND sa.attempt_id = ?
           WHERE tq.test_id = ?
           ORDER BY tq.question_order ASC";
$stmt_qa = $conn->prepare($sql_qa);
$stmt_qa->bind_param("ii", $attempt_id, $attempt['test_id']);
$stmt_qa->execute();
$questions_answers = $stmt_qa->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_qa->close();


require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Grade Attempt #<?php echo $attempt_id; ?></h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Attempt Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Test:</strong> <?php echo htmlspecialchars($attempt['test_title']); ?></p>
                    <p><strong>Student:</strong> <?php echo htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']); ?> (<?php echo htmlspecialchars($attempt['email']); ?>)</p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($attempt['status'])); ?></p>
                    <p><strong>Current Score:</strong> <?php echo number_format($attempt['score'] ?? 0, 2); ?>%</p>
                </div>
            </div>

            <?php foreach ($questions_answers as $index => $qa): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <strong>Question <?php echo $index + 1; ?></strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php echo $purifier->purify($qa['question_text']); ?>
                        </div>
                        <hr>
                        <?php if ($qa['question_type'] === 'short_answer'): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Student's Answer:</h6>
                                    <div class="p-3 bg-light border rounded">
                                        <?php echo nl2br(htmlspecialchars($qa['answer_text'] ?? 'No answer provided.')); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Model Answer:</h6>
                                    <div class="p-3 bg-light border rounded">
                                        <?php echo nl2br(htmlspecialchars($qa['model_answer'] ?? 'No model answer provided.')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <form action="grade-attempt.php?id=<?php echo $attempt_id; ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="question_id" value="<?php echo $qa['question_id']; ?>">
                                    <button type="submit" name="grade" value="1" class="btn btn-sm btn-success">Mark Correct</button>
                                    <button type="submit" name="grade" value="0" class="btn btn-sm btn-danger">Mark Incorrect</button>
                                </form>
                            </div>
                        <?php else: // For MCQs and True/False ?>
                             <h6>Student's Answer:</h6>
                             <div class="p-3 bg-light border rounded">
                                <?php
                                    if (!empty($qa['selected_option_id'])) {
                                        $opt_stmt = $conn->prepare("SELECT option_text FROM options WHERE option_id = ?");
                                        $opt_stmt->bind_param("i", $qa['selected_option_id']);
                                        $opt_stmt->execute();
                                        $selected_option = $opt_stmt->get_result()->fetch_assoc();
                                        $opt_stmt->close();
                                        echo htmlspecialchars($selected_option['option_text'] ?? 'N/A');
                                    } else {
                                        echo 'No answer provided.';
                                    }
                                ?>
                             </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-muted">
                        Current Mark:
                        <?php if ($qa['answer_score'] === '1'): ?>
                            <span class="badge bg-success">Correct</span>
                        <?php elseif ($qa['answer_score'] === '0'): ?>
                            <span class="badge bg-danger">Incorrect</span>
                        <?php else: ?>
                             <span class="badge bg-secondary">Not Yet Graded</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
             <div class="mt-4">
                <a href="results.php" class="btn btn-secondary">Back to Results</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
