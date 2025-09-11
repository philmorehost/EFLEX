<?php
$pageTitle = "Bulk Email Tool";
require_once __DIR__ . '/../includes/config.php';

// --- Auth and Role Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=authrequired");
    exit;
}
$allowed_roles = [1, 2]; // Super Admin and Admin only
if (!in_array($_SESSION['role_id'], $allowed_roles)) {
    header("Location: index.php?error=permissiondenied");
    exit;
}

$success_message = '';

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($subject) && !empty($message)) {
        // In a real application, this would queue emails to be sent.
        // For this project, we simulate the action.
        $user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $success_message = "Your message has been successfully queued for sending to " . $user_count . " users.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

<div class="d-flex" id="admin-wrapper">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">Bulk Email Tool</h2>
            </div>
            <?php include __DIR__ . '/../includes/admin_navbar_user.php'; ?>
        </nav>

        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-10">
                     <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Compose Message</h5>
                        </div>
                        <div class="card-body">
                            <form action="bulk-email.php" method="POST">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="10"></textarea>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Send to All Users</button>
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
    // Initialize CKEditor
    CKEDITOR.replace('message');
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
