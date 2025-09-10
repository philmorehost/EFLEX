<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin', 'Staff']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

$question_id = $_GET['id'] ?? null;
if (!$question_id || !filter_var($question_id, FILTER_VALIDATE_INT)) {
    $_SESSION['errors'] = ["Invalid question ID."];
    header("Location: questions.php");
    exit();
}

// --- Handle form submission for updating a question ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $options = $_POST['options'] ?? [];
    $correct_answer = trim($options[0] ?? '');

    $validation_errors = [];
    if (empty($question_text)) $validation_errors[] = 'Question text cannot be empty.';
    if (empty($category_id)) $validation_errors[] = 'Please select a category.';
    if (empty($correct_answer)) $validation_errors[] = 'The first option (Correct Answer) cannot be empty.';

    $filled_options = array_filter(array_map('trim', $options));
    if (count($filled_options) < 2) $validation_errors[] = 'Please provide at least two non-empty options.';

    if (empty($validation_errors)) {
        $options_json = json_encode(array_values($filled_options));
        $sql = "UPDATE questions SET question_text = ?, category_id = ?, options = ?, correct_answer = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$question_text, $category_id, $options_json, $correct_answer, $question_id])) {
            $_SESSION['success'] = 'Question updated successfully!';
            header("Location: questions.php");
            exit();
        } else {
            $validation_errors[] = 'Failed to update the question.';
        }
    }
    $_SESSION['errors'] = $validation_errors;
    header("Location: edit_question.php?id=" . $question_id);
    exit();
}

// --- Fetch data for the form ---
try {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        $_SESSION['errors'] = ["Question not found."];
        header("Location: questions.php");
        exit();
    }

    $categories = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $question_options = json_decode($question['options'], true) ?: [];

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Question</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger"><ul><?php foreach ($_SESSION['errors'] as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Editing Question ID: <?php echo htmlspecialchars($question['id']); ?></h6></div>
        <div class="card-body">
            <form action="edit_question.php?id=<?php echo htmlspecialchars($question_id); ?>" method="POST">
                <div class="mb-3">
                    <label for="question_text" class="form-label">Question Text</label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="5"><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" name="category_id" required>
                        <option value="">Select a category...</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($question['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr class="my-4">
                <h5 class="mb-3">Answer Options</h5>
                <p class="text-muted">The correct answer must be in the first box.</p>

                <div class="mb-3"><label class="form-label">Option 1 (Correct Answer)</label><textarea class="form-control" name="options[]" id="option1"><?php echo htmlspecialchars($question_options[0] ?? ''); ?></textarea></div>
                <div class="mb-3"><label class="form-label">Option 2</label><textarea class="form-control" name="options[]" id="option2"><?php echo htmlspecialchars($question_options[1] ?? ''); ?></textarea></div>
                <div class="mb-3"><label class="form-label">Option 3</label><textarea class="form-control" name="options[]" id="option3"><?php echo htmlspecialchars($question_options[2] ?? ''); ?></textarea></div>
                <div class="mb-3"><label class="form-label">Option 4</label><textarea class="form-control" name="options[]" id="option4"><?php echo htmlspecialchars($question_options[3] ?? ''); ?></textarea></div>

                <button type="submit" class="btn btn-primary">Update Question</button>
                <a href="questions.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    const initEditor = (id) => ClassicEditor.create(document.querySelector(`#${id}`)).catch(err => console.error(err));
    ['question_text', 'option1', 'option2', 'option3', 'option4'].forEach(initEditor);
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
