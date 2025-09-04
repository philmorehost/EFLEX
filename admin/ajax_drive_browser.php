<?php
// This file is called via AJAX to browse Google Drive folders.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/google_drive_api.php';

// Security check: ensure user is a logged-in admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    http_response_code(403);
    die('Access Denied');
}

$folder_id = isset($_GET['folder']) ? $_GET['folder'] : 'root';

$selected_files_str = isset($_GET['selected_files']) ? $_GET['selected_files'] : '';
$selected_files_arr = !empty($selected_files_str) ? explode(',', $selected_files_str) : [];

$files_data = list_google_drive_files($folder_id);
$files = $files_data['files'] ?? [];

// --- HTML Output ---
// This HTML will be injected into the modal.

if ($folder_id !== 'root') {
    // A simplified way to get the parent. A full breadcrumb trail would be more complex.
    // For now, just a "Back to Root" button. A future enhancement could be a full breadcrumb.
    echo '<button type="button" class="btn btn-outline-secondary btn-sm mb-3 drive-browse-btn" data-folder-id="root"><i class="fas fa-arrow-left"></i> Back to Root</button>';
}
?>

<div class="list-group">
    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
            <?php if ($file['mimeType'] == 'application/vnd.google-apps.folder'): ?>
                <a href="#" class="list-group-item list-group-item-action drive-browse-btn" data-folder-id="<?php echo htmlspecialchars($file['id']); ?>">
                    <i class="fas fa-folder me-2 text-warning"></i>
                    <?php echo htmlspecialchars($file['name']); ?>
                </a>
            <?php else: ?>
                <label class="list-group-item list-group-item-action d-flex align-items-center">
                    <input class="form-check-input me-3 drive-file-checkbox" type="checkbox" value="<?php echo htmlspecialchars($file['id']); ?>" <?php echo in_array($file['id'], $selected_files_arr) ? 'checked' : ''; ?>>
                    <i class="far fa-file me-2 text-secondary"></i>
                    <?php echo htmlspecialchars($file['name']); ?>
                </label>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No files or folders found in this directory.</p>
    <?php endif; ?>
</div>
