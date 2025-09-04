<?php
// Initialize session and check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'includes/db_connect.php';

// --- Local File Subscription Logic ---
$user_id = $_SESSION['id'];
$subscribed_products = [];
$files_for_product = [];
$product_name = '';

// 1. Get all active subscriptions for the user that have files
$sql_sub = "SELECT DISTINCT p.id, p.name
            FROM user_subscriptions us
            JOIN products p ON us.product_id = p.id
            JOIN product_local_files plf ON p.id = plf.product_id
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

// If user has no active subscriptions with files, redirect them.
if (empty($subscribed_products)) {
    header("location: subscribe.php");
    exit;
}

// 2. If a product is selected, get its files
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($product_id) {
    $is_subscribed_to_product = false;
    foreach($subscribed_products as $p) {
        if ($p['id'] == $product_id) {
            $is_subscribed_to_product = true;
            $product_name = $p['name'];
            break;
        }
    }

    if ($is_subscribed_to_product) {
        $sql_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ? ORDER BY original_filename ASC";
        if($stmt_files = $mysqli->prepare($sql_files)) {
            $stmt_files->bind_param("i", $product_id);
            $stmt_files->execute();
            $result_files = $stmt_files->get_result();
            $files_for_product = $result_files->fetch_all(MYSQLI_ASSOC);
            $stmt_files->close();
        }
    } else {
        $product_id = 0;
    }
}
// --- End Logic ---

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <h4>My Subscriptions</h4>
            <div class="list-group mb-4">
                <?php foreach ($subscribed_products as $product) : ?>
                    <a href="lesson_notes.php?product_id=<?php echo $product['id']; ?>"
                       class="list-group-item list-group-item-action <?php echo ($product_id == $product['id']) ? 'active' : ''; ?>">
                        <i class="fas fa-book me-2"></i>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-8">
            <?php if ($product_id) : ?>
                <h4>Files for <?php echo htmlspecialchars($product_name); ?></h4>
                <div class="list-group">
                    <?php if (count($files_for_product) > 0) : ?>
                        <?php foreach ($files_for_product as $file) : ?>
                            <a href="view_file.php?id=<?php echo $file['id']; ?>" target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-alt me-2"></i>
                                <?php echo htmlspecialchars($file['original_filename']); ?>
                                <i class="fas fa-external-link-alt float-end mt-1"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="list-group-item">No files found for this product.</div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="text-center p-5 border rounded d-flex flex-column justify-content-center align-items-center" style="min-height: 300px;">
                    <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                    <h4>Welcome to your Lesson Notes</h4>
                    <p class="text-muted">Select one of your subscriptions from the left to begin browsing files.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
