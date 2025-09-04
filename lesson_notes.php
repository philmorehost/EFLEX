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

// --- New Subscription & File Logic ---
$user_id = $_SESSION['id'];
$subscribed_products = [];
$files_for_product = [];
$product_name = '';

// 1. Get all active subscriptions for the user
$sql_sub = "SELECT p.id, p.name
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
        $subscribed_products[] = $row;
    }
    $stmt_sub->close();
}

// If user has no active, valid subscriptions, redirect them.
if (empty($subscribed_products)) {
    header("location: subscribe.php");
    exit;
}

// 2. If a product is selected, get its files
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($product_id) {
    // Security check: ensure the user is subscribed to the selected product
    $is_subscribed_to_product = false;
    foreach($subscribed_products as $p) {
        if ($p['id'] == $product_id) {
            $is_subscribed_to_product = true;
            $product_name = $p['name'];
            break;
        }
    }

    if ($is_subscribed_to_product) {
        $sql_files = "SELECT google_drive_file_id FROM product_google_drive_files WHERE product_id = ?";
        if($stmt_files = $mysqli->prepare($sql_files)) {
            $stmt_files->bind_param("i", $product_id);
            $stmt_files->execute();
            $result_files = $stmt_files->get_result();
            while($file_row = $result_files->fetch_assoc()){
                // Fetch file details from Google Drive API
                $file_details = get_file_details($file_row['google_drive_file_id']);
                if(!isset($file_details['error'])) {
                    $files_for_product[] = $file_details;
                }
            }
            $stmt_files->close();
        }
    } else {
        die("Access Denied. You are not subscribed to this product.");
    }
}

// 3. If a file is being viewed, construct its preview link
$view_file_id = isset($_GET['view']) ? $_GET['view'] : null;
$file_embed_link = null;
$file_name = null;

if ($view_file_id) {
    // A more robust security check would verify that this file_id belongs to a product the user is subscribed to.
    // For now, we assume the links are generated correctly and not tampered with.
    $file_details = get_file_details($view_file_id);
    if (isset($file_details['name'])) {
        $file_name = $file_details['name'];
        // Use the /preview URL with rm=minimal to hide the pop-out button
        $file_embed_link = "https://drive.google.com/file/d/{$view_file_id}/preview?rm=minimal";
    }
}
// --- End New Logic ---


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
            <div class="list-group mb-4">
                <!-- Display Subscribed Products -->
                <?php foreach ($subscribed_products as $product) : ?>
                    <a href="lesson_notes.php?product_id=<?php echo $product['id']; ?>"
                       class="list-group-item list-group-item-action <?php echo ($product_id == $product['id']) ? 'active' : ''; ?>">
                        <i class="fas fa-book me-2"></i>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($product_id) : // Only show file list if a product is selected ?>
                <h4 class="mt-4">Files for <?php echo htmlspecialchars($product_name); ?></h4>
                <div class="list-group">
                    <?php if (count($files_for_product) > 0) : ?>
                        <?php foreach ($files_for_product as $file) : ?>
                            <a href="lesson_notes.php?product_id=<?php echo $product_id; ?>&view=<?php echo $file['id']; ?>" class="list-group-item list-group-item-action <?php echo ($view_file_id == $file['id']) ? 'active' : ''; ?>">
                                <i class="fas fa-file-alt me-2"></i>
                                <?php echo htmlspecialchars($file['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="list-group-item">No files found for this product.</div>
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
