<?php
$pageTitle = "Question Bank";
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

// --- Fetch all questions from the database ---
$sql = "SELECT
            q.question_id,
            q.question_text,
            q.question_type,
            qc.category_name,
            u.first_name,
            u.last_name
        FROM questions q
        LEFT JOIN question_categories qc ON q.category_id = qc.category_id
        LEFT JOIN users u ON q.created_by = u.user_id
        ORDER BY q.question_id DESC";
$result = $conn->query($sql);
$questions = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';

// Function to truncate text for display
function truncate_text($text, $length = 100) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Question Bank</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row my-5">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fs-4 mb-0">All Questions</h3>
                        <a href="add-question.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Add New Question
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table bg-white rounded shadow-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Question</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Author</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($questions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No questions found in the bank.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($questions as $question): ?>
                                        <tr>
                                            <th scope="row"><?php echo $question['question_id']; ?></th>
                                            <td><?php echo htmlspecialchars(truncate_text($question['question_text'])); ?></td>
                                            <td><?php echo htmlspecialchars($question['category_name']); ?></td>
                                            <td><?php echo str_replace('_', ' ', htmlspecialchars(ucfirst($question['question_type']))); ?></td>
                                            <td><?php echo htmlspecialchars($question['first_name'] . ' ' . $question['last_name']); ?></td>
                                            <td>
                                                <a href="edit-question.php?id=<?php echo $question['question_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete-question.php?id=<?php echo $question['question_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
