<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin', 'Staff']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Note: CKEditor might add <p> tags. We should strip them for simple answers,
    // but for now, we'll trust the input is managed correctly. A library like HTML Purifier would be needed for production.
    $question_text = trim($_POST['question_text'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $options = $_POST['options'] ?? [];
    $correct_answer = trim($options[0] ?? '');
    $created_by = $_SESSION['user_id'];

    $validation_errors = [];
    if (empty($question_text)) $validation_errors[] = 'Question text cannot be empty.';
    if (empty($category_id)) $validation_errors[] = 'Please select a category.';
    if (empty($correct_answer) || $correct_answer === '<p>&nbsp;</p>') $validation_errors[] = 'The first option (Correct Answer) cannot be empty.';

    $filled_options = array_filter($options, function($opt) { return !empty(trim($opt)) && trim($opt) !== '<p>&nbsp;</p>'; });
    if (count($filled_options) < 2) $validation_errors[] = 'Please provide at least two non-empty options.';

    if (empty($validation_errors)) {
        $options_json = json_encode(array_values($filled_options));
        $sql = "INSERT INTO questions (question_text, question_type, options, correct_answer, category_id, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            if ($stmt->execute([$question_text, 'multiple_choice', $options_json, $correct_answer, $category_id, $created_by])) {
                $_SESSION['success'] = 'Question added successfully!';
            } else {
                $validation_errors[] = 'Failed to add the question.';
            }
        } catch (PDOException $e) {
            $validation_errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($validation_errors)) $_SESSION['errors'] = $validation_errors;
    header("Location: add_question.php");
    exit();
}

// --- Fetch categories for the dropdown ---
$categories = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add New Question</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Question Details</h6></div>
        <div class="card-body">
            <form action="add_question.php" method="POST">
                <div class="mb-3">
                    <label for="question_text" class="form-label">Question Text</label>
                    <textarea class="form-control" id="question_text" name="question_text" rows="5"></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" name="category_id" required><option value="">Select a category...</option><?php foreach ($categories as $category): ?><option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option><?php endforeach; ?></select>
                </div>
                <hr class="my-4">
                <h5 class="mb-3">Answer Options</h5>
                <p class="text-muted">Enter the correct answer in the first box. The answers will be randomized for students.</p>

                <div class="mb-3"><label class="form-label">Option 1 (Correct Answer)</label><textarea class="form-control" name="options[]" id="option1"></textarea></div>
                <div class="mb-3"><label class="form-label">Option 2</label><textarea class="form-control" name="options[]" id="option2"></textarea></div>
                <div class="mb-3"><label class="form-label">Option 3</label><textarea class="form-control" name="options[]" id="option3"></textarea></div>
                <div class="mb-3"><label class="form-label">Option 4</label><textarea class="form-control" name="options[]" id="option4"></textarea></div>

                <button type="submit" class="btn btn-primary">Save Question</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Helper function to initialize editors
    const initEditor = (elementId) => {
        ClassicEditor
            .create(document.querySelector(`#${elementId}`), {
                // You can configure the editor here if needed
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable' ]
            })
            .catch(error => {
                console.error(`Error initializing CKEditor for ${elementId}:`, error);
            });
    };

    // Initialize all editors
    initEditor('question_text');
    initEditor('option1');
    initEditor('option2');
    initEditor('option3');
    initEditor('option4');
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
