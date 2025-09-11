<?php
$pageTitle = "Build Test";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Get Test ID and Fetch Data ---
$test_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$test_id) {
    header("Location: tests.php?error=invalidid");
    exit;
}

// --- Handle Form Submissions (Add/Remove Questions) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);

    if (isset($_POST['add_question'])) {
        // Get max order and add new question
        $max_order_res = $conn->query("SELECT MAX(question_order) as max_order FROM test_questions WHERE test_id = $test_id")->fetch_assoc();
        $next_order = ($max_order_res['max_order'] ?? 0) + 1;

        $stmt = $conn->prepare("INSERT INTO test_questions (test_id, question_id, question_order) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $test_id, $question_id, $next_order);
        $stmt->execute();
    }

    if (isset($_POST['remove_question'])) {
        $stmt = $conn->prepare("DELETE FROM test_questions WHERE test_id = ? AND question_id = ?");
        $stmt->bind_param("ii", $test_id, $question_id);
        $stmt->execute();
    }

    // Redirect to the same page to show changes
    header("Location: build-test.php?id=$test_id");
    exit;
}

// Fetch test details
$test = $conn->query("SELECT * FROM tests WHERE test_id = $test_id")->fetch_assoc();
if (!$test) {
    header("Location: tests.php?error=notfound");
    exit;
}

// Fetch questions already in the test
$test_questions_sql = "SELECT q.question_id, q.question_text, tq.question_order
                       FROM questions q
                       JOIN test_questions tq ON q.question_id = tq.question_id
                       WHERE tq.test_id = $test_id
                       ORDER BY tq.question_order ASC";
$test_questions = $conn->query($test_questions_sql)->fetch_all(MYSQLI_ASSOC);
$test_question_ids = array_column($test_questions, 'question_id');

// Fetch available questions from the bank (not already in the test)
$bank_questions_sql = "SELECT question_id, question_text, question_type FROM questions";
if (!empty($test_question_ids)) {
    $bank_questions_sql .= " WHERE question_id NOT IN (" . implode(',', $test_question_ids) . ")";
}
$bank_questions_sql .= " ORDER BY question_id DESC";
$bank_questions = $conn->query($bank_questions_sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Build Test: <?php echo htmlspecialchars($test['title']); ?></h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <!-- Left Column: Questions in Test -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Questions in this Test (<?php echo count($test_questions); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($test_questions)): ?>
                                <p class="text-center">No questions have been added to this test yet.</p>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($test_questions as $q): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars(substr($q['question_text'], 0, 50)) . '...'; ?></span>
                                            <form method="POST" action="build-test.php?id=<?php echo $test_id; ?>">
                                                <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
                                                <button type="submit" name="remove_question" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Question Bank -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Available Questions from Bank</h5>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <?php if (empty($bank_questions)): ?>
                                <p class="text-center">No more questions available in the bank.</p>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($bank_questions as $q): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars(substr($q['question_text'], 0, 50)) . '...'; ?></strong>
                                                <small class="d-block text-muted"><?php echo str_replace('_', ' ', htmlspecialchars(ucfirst($q['question_type']))); ?></small>
                                            </div>
                                            <form method="POST" action="build-test.php?id=<?php echo $test_id; ?>">
                                                <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
                                                <button type="submit" name="add_question" class="btn btn-sm btn-outline-success">Add</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="tests.php" class="btn btn-primary">Done Building</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
