<?php
// This file is called via AJAX to browse Google Drive folders and files.
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
$all_files = $files_data['files'] ?? [];

// Separate folders and files
$folders = array_filter($all_files, function($file) {
    return $file['mimeType'] == 'application/vnd.google-apps.folder';
});
$files = array_filter($all_files, function($file) {
    return $file['mimeType'] != 'application/vnd.google-apps.folder';
});

// --- HTML Output ---
if ($folder_id !== 'root') {
    // This is a simplified breadcrumb. A real implementation would track the path.
    // For now, we just provide a "back" button functionality.
    // To get the real parent, we'd need another API call. Let's stick to a simpler "back to previous" or "back to root".
    // For simplicity, we will just go back to root. A full breadcrumb is out of scope.
    echo '<button type="button" class="btn btn-outline-secondary btn-sm mb-3 drive-browse-btn" data-folder-id="root"><i class="fas fa-arrow-left"></i> Back to Root</button>';
}
?>

<h5>Folders</h5>
<div class="list-group mb-4">
    <?php if (count($folders) > 0): ?>
        <?php foreach ($folders as $folder): ?>
            <a href="#" class="list-group-item list-group-item-action drive-browse-btn" data-folder-id="<?php echo htmlspecialchars($folder['id']); ?>">
                <i class="fas fa-folder me-2 text-warning"></i>
                <?php echo htmlspecialchars($folder['name']); ?>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No sub-folders found.</p>
    <?php endif; ?>
</div>

<h5>Files</h5>
<div class="list-group">
    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
            <label class="list-group-item">
                <input class="form-check-input me-2" type="checkbox" value="<?php echo htmlspecialchars($file['id']); ?>" data-file-name="<?php echo htmlspecialchars($file['name']); ?>">
                <i class="far fa-file-alt me-2 text-primary"></i>
                <?php echo htmlspecialchars($file['name']); ?>
            </label>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No files found in this directory.</p>
    <?php endif; ?>
</div>
