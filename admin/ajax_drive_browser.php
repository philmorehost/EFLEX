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

$files_data = list_google_drive_files($folder_id);
$files = $files_data['files'] ?? [];

// Filter for only folders
$folders = array_filter($files, function($file) {
    return $file['mimeType'] == 'application/vnd.google-apps.folder';
});

// --- HTML Output ---
// This HTML will be injected into the modal.

if ($folder_id !== 'root') {
    // A simplified way to get the parent. A full breadcrumb trail would be more complex.
    echo '<button type="button" class="btn btn-outline-secondary btn-sm mb-3 drive-browse-btn" data-folder-id="root">Back to Root</button>';
}
?>

<div class="list-group">
    <?php if (count($folders) > 0): ?>
        <?php foreach ($folders as $folder): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <a href="#" class="drive-browse-btn" data-folder-id="<?php echo htmlspecialchars($folder['id']); ?>">
                    <i class="fas fa-folder me-2"></i>
                    <?php echo htmlspecialchars($folder['name']); ?>
                </a>
                <button type="button" class="btn btn-sm btn-success select-folder-btn" data-folder-id="<?php echo htmlspecialchars($folder['id']); ?>" data-folder-name="<?php echo htmlspecialchars($folder['name']); ?>">
                    Select
                </button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No sub-folders found in this directory.</p>
    <?php endif; ?>
</div>
