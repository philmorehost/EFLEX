<?php
$pageTitle = "Import Questions";
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

$errors = [];
$success_message = '';

// --- Form Submission and CSV Processing Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"];

    // Check for errors and file type
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file. Please try again.";
    } else {
        $file_type = mime_content_type($file["tmp_name"]);
        if ($file_type !== 'text/csv' && $file_type !== 'text/plain') {
            $errors[] = "Invalid file type. Please upload a valid CSV file.";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $handle = fopen($file["tmp_name"], "r");
            $header = fgetcsv($handle); // Skip header row
            $row_count = 1;
            $imported_count = 0;
            $category_cache = [];

            while (($data = fgetcsv($handle)) !== FALSE) {
                $row_count++;

                // Map data to variables
                $category_name = trim($data[0]);
                $question_type = trim($data[1]);
                $question_text = trim($data[2]);
                $options_str = trim($data[3]);
                $corrects_str = trim($data[4]);

                // --- Validation ---
                if (empty($category_name) || empty($question_type) || empty($question_text)) {
                    throw new Exception("Row $row_count: Missing required data (category, type, or question text).");
                }
                if (!in_array($question_type, ['multiple_choice', 'true_false', 'short_answer'])) {
                    throw new Exception("Row $row_count: Invalid question type '$question_type'.");
                }

                // --- Get or Create Category ---
                if (!isset($category_cache[$category_name])) {
                    $cat_stmt = $conn->prepare("SELECT category_id FROM question_categories WHERE category_name = ?");
                    $cat_stmt->bind_param("s", $category_name);
                    $cat_stmt->execute();
                    $cat_res = $cat_stmt->get_result();
                    if ($cat_res->num_rows > 0) {
                        $category_cache[$category_name] = $cat_res->fetch_assoc()['category_id'];
                    } else {
                        $ins_cat_stmt = $conn->prepare("INSERT INTO question_categories (category_name) VALUES (?)");
                        $ins_cat_stmt->bind_param("s", $category_name);
                        $ins_cat_stmt->execute();
                        $category_cache[$category_name] = $ins_cat_stmt->insert_id;
                        $ins_cat_stmt->close();
                    }
                    $cat_stmt->close();
                }
                $category_id = $category_cache[$category_name];

                // --- Insert Question ---
                $q_stmt = $conn->prepare("INSERT INTO questions (category_id, question_type, question_text, created_by) VALUES (?, ?, ?, ?)");
                $q_stmt->bind_param("issi", $category_id, $question_type, $question_text, $_SESSION['user_id']);
                $q_stmt->execute();
                $question_id = $q_stmt->insert_id;
                $q_stmt->close();

                // --- Insert Options ---
                if ($question_type === 'multiple_choice' || $question_type === 'true_false') {
                    $options = explode('|', $options_str);
                    $are_correct = explode('|', $corrects_str);
                    if (count($options) !== count($are_correct)) {
                        throw new Exception("Row $row_count: Mismatch between number of options and correctness flags.");
                    }

                    $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                    foreach ($options as $i => $option_text) {
                        $is_correct = (isset($are_correct[$i]) && $are_correct[$i] == '1') ? 1 : 0;
                        $opt_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                        $opt_stmt->execute();
                    }
                    $opt_stmt->close();
                }
                $imported_count++;
            }
            fclose($handle);

            $conn->commit();
            $success_message = "Import successful! $imported_count questions have been added to the bank.";

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred during import: " . $e->getMessage();
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
                <h2 class="fs-2 m-0">Import Questions from CSV</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Import Failed!</strong>
                            <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">CSV Format Instructions</h5>
                        </div>
                        <div class="card-body">
                            <p>Please ensure your CSV file is formatted correctly before uploading. The file must have a header row with the following columns in this exact order:</p>
                            <p><code>category_name, question_type, question_text, options, are_correct</code></p>

                            <h6>Column Details:</h6>
                            <ul>
                                <li><strong>category_name:</strong> The name of the question category (e.g., "History"). If the category doesn't exist, it will be created automatically.</li>
                                <li><strong>question_type:</strong> Must be one of: <code>multiple_choice</code>, <code>true_false</code>, or <code>short_answer</code>.</li>
                                <li><strong>question_text:</strong> The full text of the question.</li>
                                <li><strong>options (for multiple_choice):</strong> A pipe-separated list of the answer choices. Example: <code>Paris|London|Berlin|Madrid</code></li>
                                <li><strong>are_correct (for multiple_choice/true_false):</strong> A pipe-separated list of 0s and 1s corresponding to the options. A <code>1</code> marks the correct answer. There should only be one <code>1</code>. Example: <code>1|0|0|0</code></li>
                            </ul>

                            <h6>Examples:</h6>
                            <p><strong>Multiple Choice:</strong></p>
                            <pre><code>"History","multiple_choice","What is the capital of France?","Paris|London|Berlin|Madrid","1|0|0|0"</code></pre>
                            <p><strong>True/False:</strong></p>
                            <pre><code>"History","true_false","The Eiffel Tower is in London.","True|False","0|1"</code></pre>
                             <p><strong>Short Answer (options and are_correct can be left empty):</strong></p>
                            <pre><code>"General Knowledge","short_answer","What is the color of the sky on a clear day?","",""</code></pre>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Upload CSV File</h5>
                        </div>
                        <div class="card-body">
                            <form action="import-questions.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Select CSV File</label>
                                    <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Upload and Import</button>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
