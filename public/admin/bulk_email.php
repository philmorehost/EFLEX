<?php
session_start();
require_once __DIR__ . '/../../templates/header.php';

// --- Role-based Access Control ---
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
enforce_access(['Super Admin', 'Admin']);
// --- End Access Control ---

$pdo = require __DIR__ . '/../../config/database.php';

// --- Form Handling ---
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_group = $_POST['recipient_group'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $validation_errors = [];
    if (empty($recipient_group)) $validation_errors[] = 'Recipient group is required.';
    if (empty($subject)) $validation_errors[] = 'Subject is required.';
    if (empty($message) || $message === '<p>&nbsp;</p>') $validation_errors[] = 'Message cannot be empty.';

    if (empty($validation_errors)) {
        try {
            $sql = "SELECT email FROM users u JOIN roles r ON u.role_id = r.id";
            $where_clauses = [];
            if ($recipient_group === 'all_users') {
                // No extra condition needed
            } elseif ($recipient_group === 'all_admins') {
                $where_clauses[] = "r.role_name IN ('Super Admin', 'Admin')";
            } elseif ($recipient_group === 'all_staff') {
                $where_clauses[] = "r.role_name = 'Staff'";
            } else {
                 $validation_errors[] = 'Invalid recipient group.';
            }

            if(empty($validation_errors)) {
                if(!empty($where_clauses)) {
                    $sql .= " WHERE " . implode(' AND ', $where_clauses);
                }
                $stmt = $pdo->query($sql);
                $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $sent_count = 0;
                foreach ($recipients as $recipient_email) {
                    if (send_email($pdo, $recipient_email, $subject, $message)) {
                        $sent_count++;
                    }
                }
                $_SESSION['success'] = "Email processing complete. Attempted to send to " . $sent_count . " user(s). Check logs for details.";
            }

        } catch (PDOException $e) {
            $validation_errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($validation_errors)) {
        $_SESSION['errors'] = $validation_errors;
    }

    header("Location: bulk_email.php");
    exit();
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bulk Email Tool</h1>

    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Compose Message</h6></div>
        <div class="card-body">
            <form action="bulk_email.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="recipient_group" class="form-label">Recipient Group</label>
                        <select class="form-select" name="recipient_group" required>
                            <option value="">Choose a group...</option>
                            <option value="all_users">All Users</option>
                            <option value="all_admins">All Admins (Super Admin & Admin)</option>
                            <option value="all_staff">All Staff</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="10"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Bulk Email</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#message'))
        .catch(error => { console.error(error); });
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
