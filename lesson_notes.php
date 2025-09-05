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
$view_file_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// 1. Get all active subscriptions for the user that have content (either files or HTML)
$sql_sub = "SELECT DISTINCT p.id, p.name
            FROM user_subscriptions us
            JOIN products p ON us.product_id = p.id
            WHERE us.user_id = ?
              AND us.status = 'active'
              AND (us.expires_at IS NULL OR us.expires_at >= CURDATE())
              AND (EXISTS(SELECT 1 FROM product_local_files plf WHERE plf.product_id = p.id) OR p.html_content IS NOT NULL AND p.html_content != '')";

if ($stmt_sub = $mysqli->prepare($sql_sub)) {
    $stmt_sub->bind_param("i", $user_id);
    $stmt_sub->execute();
    $result = $stmt_sub->get_result();
    $subscribed_products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt_sub->close();
}

if (empty($subscribed_products)) {
    header("location: subscribe.php");
    exit;
}

// 2. If a product is selected, get its details (files and/or HTML content)
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$html_content = null;
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
        // Check for HTML content first
        $sql_html = "SELECT html_content FROM products WHERE id = ?";
        if($stmt_html = $mysqli->prepare($sql_html)) {
            $stmt_html->bind_param("i", $product_id);
            $stmt_html->execute();
            $result_html = $stmt_html->get_result();
            if($row = $result_html->fetch_assoc()) {
                $html_content = $row['html_content'];
            }
            $stmt_html->close();
        }

        // Then check for files
        $sql_files = "SELECT id, original_filename FROM product_local_files WHERE product_id = ? ORDER BY original_filename ASC";
        if($stmt_files = $mysqli->prepare($sql_files)) {
            $stmt_files->bind_param("i", $product_id);
            $stmt_files->execute();
            $result_files = $stmt_files->get_result();
            $files_for_product = $result_files->fetch_all(MYSQLI_ASSOC);
            $stmt_files->close();
        }
    } else {
        // User is not subscribed to the selected product, reset it.
        $product_id = 0;
    }
}

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
                        <i class="fas fa-book me-2"></i> <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($product_id && !empty($files_for_product)) : ?>
                <h4 class="mt-4">Files for <?php echo htmlspecialchars($product_name); ?></h4>
                <div class="list-group">
                    <?php foreach ($files_for_product as $file) : ?>
                        <a href="lesson_notes.php?product_id=<?php echo $product_id; ?>&view=<?php echo $file['id']; ?>" class="list-group-item list-group-item-action <?php echo ($view_file_id == $file['id']) ? 'active' : ''; ?>">
                            <i class="fas fa-file-alt me-2"></i> <?php echo htmlspecialchars($file['original_filename']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <?php if ($view_file_id) : ?>
                <div class="embed-responsive" style="height: 80vh; border: 1px solid #ddd;">
                    <iframe class="embed-responsive-item w-100 h-100" src="view_file.php?id=<?php echo $view_file_id; ?>"></iframe>
                </div>
            <?php elseif ($product_id && !empty($html_content)) : ?>
                <h4><?php echo htmlspecialchars($product_name); ?></h4>
                <div id="secure-content" class="secure-content">
                    <?php echo $html_content; ?>
                </div>
            <?php else : ?>
                <div class="text-center p-5 border rounded d-flex flex-column justify-content-center align-items-center" style="min-height: 300px;">
                    <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                    <h4>Welcome to your Lesson Notes</h4>
                    <p class="text-muted">Select a subscription from the list on the left to view its content.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const secureContent = document.getElementById('secure-content');

// --- Disable Right-Click ---
if (secureContent) {
    secureContent.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
}

// --- Disable Keyboard Shortcuts for Dev Tools & Saving ---
document.addEventListener('keydown', function(e) {
    // F12
    if (e.key === 'F12') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+Shift+I
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+Shift+J
    if (e.ctrlKey && e.shiftKey && e.key === 'J') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+U
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }

    // Ctrl+S
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        alert('This function is disabled for security reasons.');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
