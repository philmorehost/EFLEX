<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
enforce_access(['Super Admin', 'Admin', 'Staff']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Handle Form Submissions ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_question') {
        $question_id = $_POST['question_id'];
        if (empty($question_id)) {
            $_SESSION['errors'] = ['Invalid question ID for deletion.'];
        } else {
            $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
            if ($stmt->execute([$question_id])) {
                $_SESSION['success'] = 'Question deleted successfully!';
            } else {
                $_SESSION['errors'] = ['Failed to delete question.'];
            }
        }
    }
    header("Location: questions.php");
    exit();
}

// --- Fetch Page Data ---
try {
    $sql = "SELECT q.id, q.question_text, c.category_name, u.name as created_by FROM questions q LEFT JOIN categories c ON q.category_id = c.id LEFT JOIN users u ON q.created_by = u.id ORDER BY q.id DESC";
    $questions = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

function truncate_text(string $text, int $length = 100): string {
    $text = strip_tags($text);
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Question Bank</h1>
        <a href="<?php echo BASE_URL; ?>/admin/add_question.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Question</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Questions</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light"><tr><th>ID</th><th>Question (Snippet)</th><th>Category</th><th>Created By</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['id']); ?></td>
                                <td><?php echo htmlspecialchars(truncate_text($question['question_text'])); ?></td>
                                <td><?php echo htmlspecialchars($question['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($question['created_by'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/edit_question.php?id=<?php echo $question['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteQuestionModal" data-id="<?php echo $question['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Question Modal -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p>Are you sure you want to delete this question? This action cannot be undone.</p></div>
            <div class="modal-footer">
                <form action="questions.php" method="POST">
                    <input type="hidden" name="action" value="delete_question">
                    <input type="hidden" name="question_id" id="deleteQuestionId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Question</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteModal = document.getElementById('deleteQuestionModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var questionId = button.getAttribute('data-id');
        deleteModal.querySelector('#deleteQuestionId').value = questionId;
    });
});
</script>
