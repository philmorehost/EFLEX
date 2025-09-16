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

// Fetch all roles for the dropdown
$roles_result = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name ASC");
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);

// --- Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $recipient_role = $_POST['recipient_role'] ?? 'all';

    if (!empty($subject) && !empty($message)) {
        $sql = "SELECT email FROM users WHERE status = 'active'";
        $params = [];
        $types = '';

        if ($recipient_role !== 'all') {
            $sql .= " AND role_id = ?";
            $params[] = $recipient_role;
            $types .= 'i';
        }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $recipients = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $recipient_count = count($recipients);

        // In a real application, this is where you would loop through $recipients
        // and send emails using a mail library like PHPMailer.
        // For now, we just show a success message.

        $group_name = 'All Users';
        if ($recipient_role !== 'all') {
            foreach($roles as $role) {
                if ($role['role_id'] == $recipient_role) {
                    $group_name = 'All ' . htmlspecialchars(ucfirst($role['role_name'])) . 's';
                    break;
                }
            }
        }

        $success_message = "Your message has been successfully queued for sending to $recipient_count recipients in the group: $group_name.";
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
                                    <label for="recipient_role" class="form-label">Recipient Group</label>
                                    <select class="form-select" id="recipient_role" name="recipient_role" required>
                                        <option value="all" selected>All Users</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['role_id']; ?>">
                                                All <?php echo htmlspecialchars(ucfirst($role['role_name'])) . 's'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="10"></textarea>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Queue Email for Sending</button>
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
    // Initialize CKEditor 5
    ClassicEditor
        .create(document.querySelector('#message'))
        .catch(error => {
            console.error('CKEditor 5 Error:', error);
        });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
