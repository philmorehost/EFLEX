<?php
// Initialize session and check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/google_drive_api.php';

// --- Granular Subscription Logic ---
$user_id = $_SESSION['id'];
$subscribed_products = [];
$sql_sub = "SELECT p.id, p.name, p.google_drive_folder_id
            FROM user_subscriptions us
            JOIN products p ON us.product_id = p.id
            WHERE us.user_id = ?
            AND us.status = 'active'
            AND (us.expires_at IS NULL OR us.expires_at >= CURDATE())";

if ($stmt_sub = $mysqli->prepare($sql_sub)) {
    $stmt_sub->bind_param("i", $user_id);
    $stmt_sub->execute();
    $result = $stmt_sub->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['google_drive_folder_id'])) {
            $subscribed_products[] = $row;
        }
    }
    $stmt_sub->close();
}

// If user has no active, valid subscriptions, redirect them.
if (empty($subscribed_products)) {
    header("location: subscribe.php");
    exit;
}
// --- End Subscription Logic ---


// Get current folder and file from GET parameters
$folder_id = isset($_GET['folder']) ? $_GET['folder'] : null;
$view_file_id = isset($_GET['view']) ? $_GET['view'] : null;
$product_name = isset($_GET['product']) ? $_GET['product'] : '';

$file_embed_link = null;
$file_name = null;
$files = [];

// Security Check: Ensure the requested folder belongs to a subscribed product
if ($folder_id) {
    $is_valid_folder = false;
    foreach ($subscribed_products as $product) {
        if ($product['google_drive_folder_id'] === $folder_id) {
            $is_valid_folder = true;
            break;
        }
    }
    if (!$is_valid_folder) {
        // If the user tries to access a folder they are not subscribed to, deny access.
        // A more complex check would be needed for sub-folders. For now, we only allow access to the root folder of each subscription.
        die("Access Denied. You are not subscribed to this content.");
    }
}


// If a file is being viewed, construct its preview link
if ($view_file_id) {
    $file_details = get_file_details($view_file_id);
    if (isset($file_details['name'])) {
        $file_name = $file_details['name'];
        $file_embed_link = "https://drive.google.com/file/d/{$view_file_id}/preview";
    }
} elseif ($folder_id) {
    // If a folder is selected, list its files
    $files_data = list_google_drive_files($folder_id);
    $files = $files_data['files'] ?? [];
}


// Include the header
include 'includes/header.php';
?>
<style>
    /* Basic content protection */
    .secure-viewer { -webkit-user-select: none; user-select: none; }
    @media print { body * { display: none !important; } }
</style>

<div class="container my-5 secure-viewer">
    <div class="row">
        <div class="col-md-4">
            <h4>My Subscriptions</h4>
            <div class="list-group">
                <!-- Display Subscribed Products -->
                <?php foreach ($subscribed_products as $product) : ?>
                    <a href="lesson_notes.php?product=<?php echo urlencode($product['name']); ?>&folder=<?php echo $product['google_drive_folder_id']; ?>"
                       class="list-group-item list-group-item-action <?php echo ($folder_id == $product['google_drive_folder_id']) ? 'active' : ''; ?>">
                        <i class="fas fa-book me-2"></i>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($folder_id) : // Only show file list if a product/folder is selected ?>
                <h4 class="mt-4">Files for <?php echo htmlspecialchars($product_name); ?></h4>
                <div class="list-group">
                    <?php if (count($files) > 0) : ?>
                        <?php foreach ($files as $file) : ?>
                            <?php
                            // For now, we won't support nested folders within a product folder.
                            if ($file['mimeType'] == 'application/vnd.google-apps.folder') continue;
                            $url = "lesson_notes.php?product=" . urlencode($product_name) . "&folder=" . $folder_id . "&view=" . $file['id'];
                            ?>
                            <a href="<?php echo $url; ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-alt me-2"></i>
                                <?php echo htmlspecialchars($file['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="list-group-item">No files found in this product folder.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <?php if ($file_embed_link) : ?>
                <h4>Viewing: <?php echo htmlspecialchars($file_name ?? 'Document'); ?></h4>
                <div class="embed-responsive" style="height: 80vh; border: 1px solid #ddd;">
                    <iframe class="embed-responsive-item w-100 h-100" src="<?php echo $file_embed_link; ?>" allow="fullscreen"></iframe>
                </div>
            <?php else : ?>
                <div class="text-center p-5 border rounded d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                    <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                    <h4>Welcome to your Lesson Notes</h4>
                    <p class="text-muted">Select one of your subscriptions from the list on the left to begin browsing files.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Disable right-click
    document.addEventListener('contextmenu', event => event.preventDefault());
</script>

<?php
// Include the footer
include 'includes/footer.php';
?>
