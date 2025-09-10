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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_name = trim($_POST['test_name'] ?? '');
    $duration = filter_var($_POST['duration'] ?? 0, FILTER_VALIDATE_INT);
    $question_ids = $_POST['question_ids'] ?? [];
    $created_by = $_SESSION['user_id'];

    $validation_errors = [];
    if (empty($test_name)) $validation_errors[] = 'Test name is required.';
    if ($duration <= 0) $validation_errors[] = 'Duration must be a positive number.';
    if (count($question_ids) < 1) $validation_errors[] = 'You must select at least one question for the test.';

    if (empty($validation_errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Create the test
            $stmt = $pdo->prepare("INSERT INTO tests (test_name, duration, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$test_name, $duration, $created_by]);
            $test_id = $pdo->lastInsertId();

            // 2. Link questions to the test
            $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
            foreach ($question_ids as $question_id) {
                $stmt->execute([$test_id, $question_id]);
            }

            $pdo->commit();
            $_SESSION['success'] = 'Test created successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $validation_errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($validation_errors)) $_SESSION['errors'] = $validation_errors;
    header("Location: create_test.php");
    exit();
}

// --- Fetch questions grouped by category ---
try {
    $sql = "SELECT q.id, q.question_text, c.category_name FROM questions q LEFT JOIN categories c ON q.category_id = c.id ORDER BY c.category_name, q.id";
    $questions_raw = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $questions_grouped = [];
    foreach ($questions_raw as $q) {
        $category = $q['category_name'] ?? 'Uncategorized';
        $questions_grouped[$category][] = $q;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

function truncate_text(string $text, int $length = 100): string {
    return mb_strlen($text) > $length ? mb_substr(strip_tags($text), 0, $length) . '...' : strip_tags($text);
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Create New Test</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form action="create_test.php" method="POST">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Select Questions</h6></div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <?php foreach ($questions_grouped as $category => $questions): ?>
                            <h5><?php echo htmlspecialchars($category); ?></h5>
                            <ul class="list-group mb-4">
                                <?php foreach ($questions as $question): ?>
                                    <li class="list-group-item">
                                        <input class="form-check-input me-1" type="checkbox" name="question_ids[]" value="<?php echo $question['id']; ?>" id="q_<?php echo $question['id']; ?>">
                                        <label class="form-check-label" for="q_<?php echo $question['id']; ?>"><?php echo htmlspecialchars(truncate_text($question['question_text'])); ?></label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Test Details</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="test_name" class="form-label">Test Name</label>
                            <input type="text" class="form-control" name="test_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (in minutes)</label>
                            <input type="number" class="form-control" name="duration" required min="1">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Test</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
