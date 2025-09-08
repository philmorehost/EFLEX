<?php
$is_picker_mode = isset($_GET['mode']) && $_GET['mode'] === 'picker';

if (!$is_picker_mode) {
    include 'includes/header.php';
} else {
    // For picker mode, we need a minimal HTML structure
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once '../includes/db_connect.php';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Google Drive Picker</title>';
    // You might want to link to a minimal version of your CSS or Bootstrap here if needed
    echo '<link href="../css/bootstrap.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';
    echo '</head><body><div class="container-fluid pt-3">';
}

require_once '../includes/google_drive_api.php';

// Fetch files from Google Drive
$folder_id = isset($_GET['folder']) ? $_GET['folder'] : 'root';
$files_data = list_google_drive_files($folder_id);

$files = [];
if(isset($files_data['files'])){
    $files = $files_data['files'];
} elseif(isset($files_data['error'])) {
    $error_message = $files_data['error']['message'] ?? 'An unknown error occurred while fetching files from Google Drive.';
}

?>

<?php if(!$is_picker_mode): ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Manage Google Drive Files</h1>
</div>
<?php endif; ?>

<?php
// Handle securing files
if (isset($_POST['secure_folder']) && !$is_picker_mode) {
    // ... (rest of the securing logic remains the same)
}
?>

<?php
if (isset($files_data['error'])) {
    $error_message = 'An error occurred while communicating with the Google Drive API. ';
    $error_message .= 'Details: <pre>' . htmlspecialchars(json_encode($files_data['error'], JSON_PRETTY_PRINT)) . '</pre>';
    echo '<div class="alert alert-danger">' . $error_message . '</div>';
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fab fa-google-drive"></i> Files and Folders</span>
        <?php if(!$is_picker_mode): ?>
        <form action="manage_drive.php?folder=<?php echo $folder_id; ?>" method="post">
            <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>">
            <button type="submit" name="secure_folder" class="btn btn-sm btn-danger">
                <i class="fas fa-lock"></i> Secure All Files in This Folder
            </button>
        </form>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if(!$is_picker_mode): ?>
        <p>This page shows files from your Google Drive. The "Secure All Files" button will apply a content protection flag to all files (not folders) in the currently viewed folder to prevent viewers from downloading, printing, or copying them.</p>
        <?php else: ?>
        <p>Select a file to associate it with the product.</p>
        <?php endif; ?>
        <div class="list-group">
            <?php if($folder_id !== 'root'): ?>
                 <a href="manage_drive.php?folder=root<?php if($is_picker_mode) echo '&mode=picker'; ?>" class="list-group-item list-group-item-action"><i class="fas fa-arrow-left"></i> Back to Root</a>
            <?php endif; ?>
            <?php if(count($files) > 0): ?>
                <?php foreach($files as $file): ?>
                    <?php
                        $is_folder = $file['mimeType'] == 'application/vnd.google-apps.folder';
                        $link = '#';
                        $onclick = '';
                        if ($is_folder) {
                            $link = 'manage_drive.php?folder=' . $file['id'];
                            if ($is_picker_mode) {
                                $link .= '&mode=picker';
                            }
                        } elseif ($is_picker_mode) {
                            $onclick = "selectFile('{$file['id']}', '" . htmlspecialchars($file['name'], ENT_QUOTES) . "', '{$file['mimeType']}'); return false;";
                        }
                    ?>
                    <a href="<?php echo $link; ?>" <?php if($onclick) echo "onclick=\"{$onclick}\""; ?> class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas <?php echo $is_folder ? 'fa-folder' : 'fa-file'; ?> me-2"></i>
                            <?php echo htmlspecialchars($file['name']); ?>
                        </div>
                        <small class="text-muted"><?php echo htmlspecialchars($file['mimeType']); ?></small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item">No files or folders found in this directory.</div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if($is_picker_mode): ?>
<script>
function selectFile(fileId, fileName, mimeType) {
    // Send the selected file info to the parent window
    window.parent.postMessage({
        source: 'googleDrivePicker',
        file: {
            id: fileId,
            name: fileName,
            mimeType: mimeType
        }
    }, '*'); // Be more specific with the target origin in a real app
}
</script>
<?php endif; ?>

<?php
if (!$is_picker_mode) {
    include 'includes/footer.php';
} else {
    echo '</div></body></html>';
}
?>
