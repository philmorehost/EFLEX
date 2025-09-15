<?php
$pageTitle = "Edit Question";
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/lib/htmlpurifier/library/HTMLPurifier.standalone.php';

// --- HTML Purifier Setup ---
$purifier_config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($purifier_config);

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

// --- Get Question ID and Fetch Data ---
$question_id_to_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$question_id_to_edit) {
    header("Location: questions.php?error=invalidid");
    exit;
}

// Fetch question data
$stmt = $conn->prepare("SELECT * FROM questions WHERE question_id = ?");
$stmt->bind_param("i", $question_id_to_edit);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$question) {
    header("Location: questions.php?error=q_not_found");
    exit;
}

// Fetch categories and options if applicable
$categories = $conn->query("SELECT * FROM question_categories ORDER BY category_name ASC")->fetch_all(MYSQLI_ASSOC);
$options = [];
if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
    $opt_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");
    $opt_stmt->bind_param("i", $question_id_to_edit);
    $opt_stmt->execute();
    $options = $opt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $opt_stmt->close();
}

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'];
    $question_text = $purifier->purify(trim($_POST['question_text']));

    if (empty($category_id) || empty($question_text)) {
        $errors[] = "Category and question text are required.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE questions SET category_id = ?, question_text = ? WHERE question_id = ?");
            $stmt->bind_param("isi", $category_id, $question_text, $question_id_to_edit);
            $stmt->execute();
            $stmt->close();

            // Delete old options before inserting new ones
            $del_stmt = $conn->prepare("DELETE FROM options WHERE question_id = ?");
            $del_stmt->bind_param("i", $question_id_to_edit);
            $del_stmt->execute();
            $del_stmt->close();

            if ($question['question_type'] === 'multiple_choice') {
                $posted_options = $_POST['options'] ?? [];
                $correct_option_index = $_POST['is_correct'] ?? -1;
                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                foreach ($posted_options as $index => $option_text) {
                    if (!empty(trim($option_text))) {
                        $is_correct = ($index == $correct_option_index) ? 1 : 0;
                        $purified_option_text = $purifier->purify(trim($option_text));
                        $opt_stmt->bind_param("isi", $question_id_to_edit, $purified_option_text, $is_correct);
                        $opt_stmt->execute();
                    }
                }
                $opt_stmt->close();
            } elseif ($question['question_type'] === 'true_false') {
                $correct_answer = $_POST['is_correct_tf'] ?? '';
                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $option_text_true = 'True';
                $is_correct_true = ($correct_answer === 'true') ? 1 : 0;
                $opt_stmt->bind_param("isi", $question_id_to_edit, $option_text_true, $is_correct_true);
                $opt_stmt->execute();
                $option_text_false = 'False';
                $is_correct_false = ($correct_answer === 'false') ? 1 : 0;
                $opt_stmt->bind_param("isi", $question_id_to_edit, $option_text_false, $is_correct_false);
                $opt_stmt->execute();
                $opt_stmt->close();
            }

            $conn->commit();
            header("Location: questions.php?success=q_updated");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Edit Question</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Editing Question #<?php echo $question['question_id']; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="edit-question.php?id=<?php echo $question_id_to_edit; ?>" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $question['category_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="question_type" class="form-label">Question Type</label>
                                        <input type="text" class="form-control" value="<?php echo str_replace('_', ' ', htmlspecialchars(ucfirst($question['question_type']))); ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="question_text" class="form-label">Question Text</label>
                                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                                </div>

                                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                <div class="mt-4 p-3 border rounded">
                                    <h5>Multiple Choice Options</h5>
                                    <div id="mc-options-wrapper">
                                        <?php foreach ($options as $index => $option): ?>
                                        <div class="input-group mb-2">
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0" type="radio" value="<?php echo $index; ?>" name="is_correct" <?php echo $option['is_correct'] ? 'checked' : ''; ?>>
                                            </div>
                                            <input type="text" class="form-control" name="options[]" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" id="add-mc-option" class="btn btn-sm btn-outline-secondary mt-2">Add Another Option</button>
                                </div>
                                <?php elseif ($question['question_type'] === 'true_false'): ?>
                                <div class="mt-4 p-3 border rounded">
                                    <h5>Correct Answer</h5>
                                    <?php
                                    $correct_tf_answer = '';
                                    foreach ($options as $option) {
                                        if ($option['is_correct']) {
                                            $correct_tf_answer = strtolower($option['option_text']);
                                        }
                                    }
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_correct_tf" value="true" <?php echo ($correct_tf_answer === 'true') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">True</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_correct_tf" value="false" <?php echo ($correct_tf_answer === 'false') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">False</label>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <a href="questions.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($question['question_type'] === 'multiple_choice'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let optionIndex = <?php echo count($options); ?>;
    document.getElementById('add-mc-option').addEventListener('click', function() {
        const wrapper = document.getElementById('mc-options-wrapper');
        const newOption = document.createElement('div');
        newOption.className = 'input-group mb-2';
        newOption.innerHTML = `
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="${optionIndex}" name="is_correct">
            </div>
            <input type="text" class="form-control" name="options[]" placeholder="New Option" required>
        `;
        wrapper.appendChild(newOption);
        optionIndex++;
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
