<?php
$pageTitle = "Add New Question";
require_once __DIR__ . '/../includes/config.php';

// --- Authentication and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2, 3]; // Super Admin, Admin, Staff
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

// --- Fetch Categories for Dropdown ---
$categories_result = $conn->query("SELECT * FROM question_categories ORDER BY category_name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'];
    $question_type = $_POST['question_type'];
    $question_text = trim($_POST['question_text']);
    $created_by = $_SESSION['user_id'];

    // Validation
    if (empty($category_id) || empty($question_type) || empty($question_text)) {
        $errors[] = "Category, question type, and question text are required.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            // Insert into questions table
            $stmt = $conn->prepare("INSERT INTO questions (category_id, question_type, question_text, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $category_id, $question_type, $question_text, $created_by);
            $stmt->execute();
            $question_id = $stmt->insert_id;
            $stmt->close();

            // Handle options based on question type
            if ($question_type === 'multiple_choice') {
                $options = $_POST['options'] ?? [];
                $correct_option_index = $_POST['is_correct'] ?? -1;

                if (empty($options) || $correct_option_index == -1) {
                     throw new Exception("Multiple choice questions require at least one option and a correct answer.");
                }

                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                foreach ($options as $index => $option_text) {
                    if (!empty(trim($option_text))) {
                        $is_correct = ($index == $correct_option_index) ? 1 : 0;
                        $opt_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                        $opt_stmt->execute();
                    }
                }
                $opt_stmt->close();
            } elseif ($question_type === 'true_false') {
                $correct_answer = $_POST['is_correct_tf'] ?? '';

                if ($correct_answer !== 'true' && $correct_answer !== 'false') {
                    throw new Exception("A correct answer must be selected for True/False questions.");
                }

                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");

                // Insert True option
                $is_correct_true = ($correct_answer === 'true') ? 1 : 0;
                $option_text_true = 'True';
                $opt_stmt->bind_param("isi", $question_id, $option_text_true, $is_correct_true);
                $opt_stmt->execute();

                // Insert False option
                $is_correct_false = ($correct_answer === 'false') ? 1 : 0;
                $option_text_false = 'False';
                $opt_stmt->bind_param("isi", $question_id, $option_text_false, $is_correct_false);
                $opt_stmt->execute();

                $opt_stmt->close();
            }

            $conn->commit();
            header("Location: questions.php?success=q_added");
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
                <h2 class="fs-2 m-0">Add New Question</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Question Details</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="add-question.php" method="POST" id="question-form">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select a category...</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="question_type" class="form-label">Question Type</label>
                                        <select class="form-select" id="question_type" name="question_type" required>
                                            <option value="">Select a type...</option>
                                            <option value="multiple_choice">Multiple Choice</option>
                                            <option value="true_false">True / False</option>
                                            <option value="short_answer">Short Answer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="question_text" class="form-label">Question Text</label>
                                    <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                                </div>

                                <!-- Dynamic fields based on question type -->
                                <div id="dynamic-fields-container"></div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Save Question</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('question_type');
    const container = document.getElementById('dynamic-fields-container');

    typeSelect.addEventListener('change', function() {
        container.innerHTML = ''; // Clear previous fields
        const type = this.value;

        if (type === 'multiple_choice') {
            container.innerHTML = `
                <div class="mt-4 p-3 border rounded">
                    <h5>Multiple Choice Options</h5>
                    <div id="mc-options-wrapper">
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" value="0" name="is_correct" checked>
                            </div>
                            <input type="text" class="form-control" name="options[]" placeholder="Option 1" required>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" value="1" name="is_correct">
                            </div>
                            <input type="text" class="form-control" name="options[]" placeholder="Option 2" required>
                        </div>
                    </div>
                    <button type="button" id="add-mc-option" class="btn btn-sm btn-outline-secondary mt-2">Add Another Option</button>
                </div>
            `;

            let optionIndex = 2;
            document.getElementById('add-mc-option').addEventListener('click', function() {
                const wrapper = document.getElementById('mc-options-wrapper');
                const newOption = document.createElement('div');
                newOption.className = 'input-group mb-2';
                newOption.innerHTML = `
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="radio" value="${optionIndex}" name="is_correct">
                    </div>
                    <input type="text" class="form-control" name="options[]" placeholder="Option ${optionIndex + 1}" required>
                `;
                wrapper.appendChild(newOption);
                optionIndex++;
            });

        } else if (type === 'true_false') {
            container.innerHTML = `
                <div class="mt-4 p-3 border rounded">
                    <h5>Correct Answer</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_correct_tf" id="is_correct_true" value="true" checked>
                        <label class="form-check-label" for="is_correct_true">True</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_correct_tf" id="is_correct_false" value="false">
                        <label class="form-check-label" for="is_correct_false">False</label>
                    </div>
                </div>
            `;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
