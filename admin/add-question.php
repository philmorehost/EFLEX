<?php
$pageTitle = "Add Questions";
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

// --- Fetch Categories for Dropdown ---
$categories_result = $conn->query("SELECT * FROM question_categories ORDER BY category_name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_questions = $_POST['questions'] ?? [];
    $created_by = $_SESSION['user_id'];

    if (empty($posted_questions)) {
        $errors[] = "No questions were submitted.";
    } else {
        $conn->begin_transaction();
        try {
            foreach ($posted_questions as $q_idx => $q_data) {
                $category_id = $q_data['category_id'];
                $question_type = $q_data['question_type'];
                $question_text = trim($q_data['question_text']);

                if (empty($category_id) || empty($question_type) || empty($question_text)) {
                    throw new Exception("All fields are required for Question #" . ($q_idx + 1));
                }

                $stmt = $conn->prepare("INSERT INTO questions (category_id, question_type, question_text, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $category_id, $question_type, $question_text, $created_by);
                $stmt->execute();
                $question_id = $stmt->insert_id;
                $stmt->close();

                if ($question_type === 'multiple_choice') {
                    $options = $q_data['options'] ?? [];
                    $correct_option_index = $q_data['is_correct'] ?? -1;
                    if (empty($options) || $correct_option_index == -1) throw new Exception("MCQ must have options and a correct answer for Question #" . ($q_idx + 1));

                    $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                    foreach ($options as $opt_idx => $option_text) {
                        if (!empty(trim($option_text))) {
                            $is_correct = ($opt_idx == $correct_option_index) ? 1 : 0;
                            $opt_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                            $opt_stmt->execute();
                        }
                    }
                    $opt_stmt->close();
                } elseif ($question_type === 'true_false') {
                    $correct_answer = $q_data['is_correct_tf'] ?? '';
                    if ($correct_answer !== 'true' && $correct_answer !== 'false') throw new Exception("Correct answer must be selected for True/False Question #" . ($q_idx + 1));

                    $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                    $opt_stmt->bind_param("isi", $question_id, 'True', ($correct_answer === 'true'));
                    $opt_stmt->execute();
                    $opt_stmt->bind_param("isi", $question_id, 'False', ($correct_answer === 'false'));
                    $opt_stmt->execute();
                    $opt_stmt->close();
                }
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
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Add Questions in Bulk</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-12">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form action="add-question.php" method="POST" id="bulk-question-form">
                        <div id="questions-container">
                            <!-- Question Block Template (will be cloned) -->
                            <div class="card shadow-sm mb-4 question-block">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Question 1</h5>
                                    <button type="button" class="btn-close" aria-label="Close" onclick="removeQuestionBlock(this)"></button>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="questions[0][category_id]" required>
                                                <option value="">Select a category...</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Question Type</label>
                                            <select class="form-select question-type-select" name="questions[0][question_type]" required>
                                                <option value="">Select a type...</option>
                                                <option value="multiple_choice">Multiple Choice</option>
                                                <option value="true_false">True / False</option>
                                                <option value="short_answer">Short Answer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Question Text</label>
                                        <div class="editor-wrapper">
                                            <textarea class="form-control" name="questions[0][question_text]" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="dynamic-fields-container"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="add-question-btn" class="btn btn-info text-white">Add Another Question</button>
                            <button type="submit" class="btn btn-primary">Save All Questions</button>
                            <a href="questions.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let questionIndex = 1;
let editors = {}; // Keep track of editor instances

function initializeCKEditor(element) {
    ClassicEditor
        .create(element)
        .then(editor => {
            // Store the editor instance using a unique key
            editors[element.id] = editor;
        })
        .catch(error => {
            console.error('CKEditor 5 Error:', error);
        });
}

function handleQuestionTypeChange(selectElement) {
    const container = selectElement.closest('.question-block').querySelector('.dynamic-fields-container');
    container.innerHTML = '';
    const type = selectElement.value;
    const qIndex = selectElement.name.match(/\[(\d+)\]/)[1];

    if (type === 'multiple_choice') {
        const mcHtml = `
            <div class="mt-4 p-3 border rounded">
                <h5>Multiple Choice Options</h5>
                <div class="mc-options-wrapper">
                    <div class="input-group mb-2"><div class="input-group-text"><input class="form-check-input mt-0" type="radio" value="0" name="questions[${qIndex}][is_correct]" checked></div><input type="text" class="form-control" name="questions[${qIndex}][options][]" placeholder="Option 1" required></div>
                    <div class="input-group mb-2"><div class="input-group-text"><input class="form-check-input mt-0" type="radio" value="1" name="questions[${qIndex}][is_correct]"></div><input type="text" class="form-control" name="questions[${qIndex}][options][]" placeholder="Option 2" required></div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-mc-option-btn">Add Another Option</button>
            </div>`;
        container.innerHTML = mcHtml;
    } else if (type === 'true_false') {
        container.innerHTML = `
            <div class="mt-4 p-3 border rounded">
                <h5>Correct Answer</h5>
                <div class="form-check"><input class="form-check-input" type="radio" name="questions[${qIndex}][is_correct_tf]" value="true" checked><label class="form-check-label">True</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="questions[${qIndex}][is_correct_tf]" value="false"><label class="form-check-label">False</label></div>
            </div>`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const firstTextarea = document.querySelector('textarea[name="questions[0][question_text]"]');
    firstTextarea.id = 'editor-0';
    initializeCKEditor(firstTextarea);

    document.getElementById('questions-container').addEventListener('change', e => {
        if (e.target && e.target.classList.contains('question-type-select')) handleQuestionTypeChange(e.target);
    });

    document.getElementById('questions-container').addEventListener('click', e => {
        if (e.target && e.target.classList.contains('add-mc-option-btn')) {
            const wrapper = e.target.previousElementSibling;
            const qIndex = wrapper.querySelector('input[type="radio"]').name.match(/\[(\d+)\]/)[1];
            let optionCount = wrapper.querySelectorAll('.input-group').length;
            const newOption = document.createElement('div');
            newOption.className = 'input-group mb-2';
            newOption.innerHTML = `<div class="input-group-text"><input class="form-check-input mt-0" type="radio" value="${optionCount}" name="questions[${qIndex}][is_correct]"></div><input type="text" class="form-control" name="questions[${qIndex}][options][]" placeholder="Option ${optionCount + 1}" required>`;
            wrapper.appendChild(newOption);
        }
    });

    document.getElementById('add-question-btn').addEventListener('click', function() {
        const container = document.getElementById('questions-container');
        const firstBlock = container.querySelector('.question-block');
        const newBlock = firstBlock.cloneNode(true);

        newBlock.querySelector('h5').textContent = `Question ${questionIndex + 1}`;
        newBlock.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${questionIndex}]`);
        });

        newBlock.querySelector('.dynamic-fields-container').innerHTML = '';
        const editorWrapper = newBlock.querySelector('.editor-wrapper');
        const newTextareaId = `editor-${questionIndex}`;
        editorWrapper.innerHTML = `<textarea class="form-control" name="questions[${questionIndex}][question_text]" rows="3" id="${newTextareaId}"></textarea>`;

        container.appendChild(newBlock);
        initializeCKEditor(document.getElementById(newTextareaId));
        questionIndex++;
    });
});

function removeQuestionBlock(button) {
    if (document.querySelectorAll('.question-block').length > 1) {
        const block = button.closest('.question-block');
        const textarea = block.querySelector('textarea');
        if (textarea && editors[textarea.id]) {
            editors[textarea.id].destroy().then(() => {
                delete editors[textarea.id];
                block.remove();
            });
        } else {
            block.remove();
        }
    } else {
        alert("You must have at least one question.");
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
