<?php
$pageTitle = "Create New Test";
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

$errors = [];

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $time_limit = filter_input(INPUT_POST, 'time_limit_minutes', FILTER_VALIDATE_INT);
    $passing_score = filter_input(INPUT_POST, 'passing_score', FILTER_VALIDATE_INT);
    $available_from = trim($_POST['available_from'] ?? '');
    $available_to = trim($_POST['available_to'] ?? '');
    $created_by = $_SESSION['user_id'];

    if (empty($title)) {
        $errors[] = "Test title is required.";
    }
    if ($time_limit === false || $time_limit < 0) {
        $errors[] = "Time limit must be a positive number.";
    }
    if ($passing_score === false || $passing_score < 0 || $passing_score > 100) {
        $errors[] = "Passing score must be between 0 and 100.";
    }
    if (!empty($available_from) && !empty($available_to) && strtotime($available_from) >= strtotime($available_to)) {
        $errors[] = "The 'Available From' date must be earlier than the 'Available To' date.";
    }

    if (empty($errors)) {
        // Set empty strings to NULL for the database
        $available_from = !empty($available_from) ? $available_from : null;
        $available_to = !empty($available_to) ? $available_to : null;

        $stmt = $conn->prepare("INSERT INTO tests (title, description, time_limit_minutes, passing_score, created_by, available_from, available_to) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiiss", $title, $description, $time_limit, $passing_score, $created_by, $available_from, $available_to);

        if ($stmt->execute()) {
            $new_test_id = $stmt->insert_id;
            // Redirect to the test builder to add questions
            header("Location: build-test.php?id=" . $new_test_id . "&success=test_created");
            exit;
        } else {
            $errors[] = "Failed to create test. Please try again.";
        }
        $stmt->close();
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
                <h2 class="fs-2 m-0">Create New Test</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Test Details</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form action="create-test.php" method="POST">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Test Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="time_limit_minutes" class="form-label">Time Limit (minutes)</label>
                                        <input type="number" class="form-control" id="time_limit_minutes" name="time_limit_minutes" value="60" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="passing_score" class="form-label">Passing Score (%)</label>
                                        <input type="number" class="form-control" id="passing_score" name="passing_score" value="70" min="0" max="100" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="available_from" class="form-label">Available From (Optional)</label>
                                        <input type="datetime-local" class="form-control" id="available_from" name="available_from">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="available_to" class="form-label">Available To (Optional)</label>
                                        <input type="datetime-local" class="form-control" id="available_to" name="available_to">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Save and Add Questions</button>
                                    <a href="tests.php" class="btn btn-secondary">Cancel</a>
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
